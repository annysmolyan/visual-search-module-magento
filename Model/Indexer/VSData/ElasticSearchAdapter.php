<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Indexer\VSData;

use Exception;
use Magento\AdvancedSearch\Helper\Data;
use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\ArrayManager;
use OpenSearch\Common\Exceptions\Missing404Exception;
use Psr\Log\LoggerInterface;

/**
 * Class ElasticSearchAdapter
 * Class was created according to \Magento\Elasticsearch8\Model\Adapter\Elasticsearch
 * Created custom class, because magento uses product data in the original class.
 * @package BelSmol\VisualSearch\Model\Indexer\VSData
 */
class ElasticSearchAdapter
{
    /**#@+
     * Text flags for Elasticsearch bulk actions
     */
    public const BULK_ACTION_INDEX = 'index';
    public const BULK_ACTION_CREATE = 'create';
    public const BULK_ACTION_DELETE = 'delete';
    public const BULK_ACTION_UPDATE = 'update';

    /**
     * Buffer for total fields limit in mapping.
     */
    private const MAPPING_TOTAL_FIELDS_BUFFER_LIMIT = 1000;

    protected const ENTITY_TYPE = 'entityType'; //added const

    /**
     * @param ConnectionManager $connectionManager
     * @param FieldMapperInterface $fieldMapper
     * @param Config $clientConfig
     * @param BuilderInterface $indexBuilder
     * @param LoggerInterface $logger
     * @param IndexNameResolver $indexNameResolver
     * @param Data $helper
     * @param BatchDataMapperInterface $batchDocumentDataMapper
     * @param ArrayManager|null $arrayManager
     * @param ClientInterface|null $client
     * @param array $options
     * @param array $preparedIndex
     * @param array $indexByCode
     * @param array $mappedAttributes
     * @param array $responseErrorExceptionList
     * @param string $indexerId
     * @throws LocalizedException
     */
    public function __construct(
        protected ConnectionManager $connectionManager,
        protected FieldMapperInterface $fieldMapper,
        protected Config $clientConfig,
        protected BuilderInterface $indexBuilder,
        protected LoggerInterface $logger,
        protected IndexNameResolver $indexNameResolver,
        protected Data $helper,
        private BatchDataMapperInterface $batchDocumentDataMapper,
        private ?ArrayManager $arrayManager = null,
        protected ?ClientInterface $client = null,
        private array $options = [],
        protected array $preparedIndex = [],
        private array $indexByCode = [],
        private array $mappedAttributes = [],
        private array $responseErrorExceptionList = ['elasticsearchMissing404' => Missing404Exception::class],
        private string $indexerId = ''
    ) {
        $this->arrayManager = $arrayManager ?: ObjectManager::getInstance()->get(ArrayManager::class);

        try {
            $this->client = $this->connectionManager->getConnection($options);
        } catch (Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('The search failed because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * NEW METHOD
     * Was added because magento doesn't provide functionality to inject custom index during reindex process
     * @param string $indexerId
     */
    public function setIndexerId(string $indexerId): void
    {
        $this->indexerId = $indexerId;
    }

    /**
     * Retrieve Elasticsearch server status
     * Copied from original class
     * @return bool
     * @throws LocalizedException
     */
    public function ping(): bool
    {
        try {
            $response = $this->client->ping();
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Could not ping search engine: %1', $e->getMessage())
            );
        }
        return $response;
    }

    /**
     * @OVERRIDE
     * Create Elasticsearch documents by specified data
     * @param array $documentData
     * @param $storeId
     * @return array
     */
    public function prepareDocsPerStore(array $documentData, $storeId): array
    {
        $documents = [];

        if (count($documentData)) {
            $documents = $this->batchDocumentDataMapper->map(
                $documentData,
                $storeId,
                [self::ENTITY_TYPE => $this->indexerId] // ->added this parameter
            );
        }

        return $documents;
    }

    /**
     * Add prepared Elasticsearch documents to Elasticsearch index
     * Original method. DON'T USE STRICT TYPE HERE for storeId
     *
     * @param array $documents
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     * @throws Exception
     */
    public function addDocs(array $documents, $storeId, $mappedIndexerId): self
    {
        if (count($documents)) {
            try {
                $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
                $bulkIndexDocuments = $this->getDocsArrayInBulkIndexFormat($documents, $indexName);
                $this->client->bulkQuery($bulkIndexDocuments);
            } catch (Exception $e) {
                $this->logger->critical($e);
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Removes all documents from Elasticsearch index
     * Original method. DON'T USE STRICT TYPE HERE
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     */
    public function cleanIndex($storeId, $mappedIndexerId): self
    {
        // needed to fix bug with double indices in alias because of second reindex in same process
        unset($this->preparedIndex[$storeId]);

        $this->checkIndex($storeId, $mappedIndexerId, true);
        $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);

        // prepare new index name and increase version
        $indexPattern = $this->indexNameResolver->getIndexPattern($storeId, $mappedIndexerId);
        $version = (int)(str_replace($indexPattern, '', $indexName));

        // compatibility with snapshotting collision
        $deleteQueue = [];
        do {
            $newIndexName = $indexPattern . (++$version);
            if ($this->client->indexExists($newIndexName)) {
                $deleteQueue[]= $newIndexName;
                $indexExists = true;
            } else {
                $indexExists = false;
            }
        } while ($indexExists);

        foreach ($deleteQueue as $indexToDelete) {
            // remove index if already exists, wildcard deletion may cause collisions
            try {
                $this->client->deleteIndex($indexToDelete);
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }

        // prepare new index
        $this->prepareIndex($storeId, $newIndexName, $mappedIndexerId);

        return $this;
    }

    /**
     * Delete documents from Elasticsearch index by Ids
     * Original method. DON'T USE STRICT TYPE HERE
     * @param array $documentIds
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     * @throws Exception
     */
    public function deleteDocs(array $documentIds, $storeId, $mappedIndexerId): self
    {
        try {
            $this->checkIndex($storeId, $mappedIndexerId, false);
            $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
            $bulkDeleteDocuments = $this->getDocsArrayInBulkIndexFormat(
                $documentIds,
                $indexName,
                self::BULK_ACTION_DELETE
            );
            $this->client->bulkQuery($bulkDeleteDocuments);
        } catch (Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Reformat documents array to bulk format
     * Original method. DON'T USE STRICT TYPE HERE
     *
     * @param array $documents
     * @param string $indexName
     * @param string $action
     * @return array
     */
    public function getDocsArrayInBulkIndexFormat(
        $documents,
        $indexName,
        $action = self::BULK_ACTION_INDEX
    ): array {
        $bulkArray = [
            'index' => $indexName,
            'body' => [],
            'refresh' => true,
        ];

        foreach ($documents as $id => $document) {
            $bulkArray['body'][] = [
                $action => [
                    '_id' => $id,
                    '_index' => $indexName
                ]
            ];

            if ($action == self::BULK_ACTION_INDEX) {
                $bulkArray['body'][] = $document;
            }
        }

        return $bulkArray;
    }

    /**
     * Original method. DON'T USE STRICT TYPE HERE
     * @param $storeId
     * @param $mappedIndexerId
     * @param bool $checkAlias
     * @return $this
     */
    public function checkIndex(
        $storeId,
        $mappedIndexerId,
        bool $checkAlias = true
    ): self {
        // create new index for store
        $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
        if (!$this->client->indexExists($indexName)) {
            $this->prepareIndex($storeId, $indexName, $mappedIndexerId);
        }

        // add index to alias
        if ($checkAlias) {
            $namespace = $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId);
            if (!$this->client->existsAlias($namespace, $indexName)) {
                $this->client->updateAlias($namespace, $indexName);
            }
        }
        return $this;
    }

    /**
     * Update Elasticsearch alias for new index.
     * Original method. DON'T USE STRICT TYPE HERE
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     */
    public function updateAlias($storeId, $mappedIndexerId): self
    {
        if (!isset($this->preparedIndex[$storeId])) {
            return $this;
        }

        $oldIndex = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        if ($oldIndex == $this->preparedIndex[$storeId]) {
            $oldIndex = '';
        }

        $this->client->updateAlias(
            $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId),
            $this->preparedIndex[$storeId],
            $oldIndex
        );

        // remove obsolete index
        if ($oldIndex) {
            try {
                $this->client->deleteIndex($oldIndex);
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
            unset($this->indexByCode[$mappedIndexerId . '_' . $storeId]);
        }

        return $this;
    }

    /**
     * @OVERRIDE
     * Update Elasticsearch mapping for index.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @param string $attributeCode
     * @return $this
     */
    public function updateIndexMapping(int $storeId, string $mappedIndexerId, string $attributeCode): self
    {
        $indexName = $this->getIndexFromAlias($storeId, $mappedIndexerId);
        if (empty($indexName)) {
            return $this;
        }

        //>>> UPD START
        $newAttributeMapping = [];
        $mappedAttributes = $this->getMappedAttributes($indexName);

        $attrToUpdate = array_diff_key($newAttributeMapping, $mappedAttributes);
        if (!empty($attrToUpdate)) {
            $settings['index']['mapping']['total_fields']['limit'] = $this
                ->getMappingTotalFieldsLimit(array_merge($mappedAttributes, $attrToUpdate));
            $this->client->putIndexSettings($indexName, ['settings' => $settings]);

            $this->client->addFieldsMapping(
                $attrToUpdate,
                $indexName,
                $this->clientConfig->getEntityType()
            );
            $this->setMappedAttributes($indexName, $attrToUpdate);
        }

        //>>> UPD END
        return $this;
    }

    /**
     * Check if the given class name is in the exception list
     * Original method.
     * @param Exception $exception
     * @return bool
     */
    private function validateException(Exception $exception): bool
    {
        return in_array(get_class($exception), $this->responseErrorExceptionList, true);
    }


    /**
     * Retrieve index definition from class.
     * Original method.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return string
     */
    private function getIndexFromAlias(int $storeId, string $mappedIndexerId): string
    {
        $indexCode = $mappedIndexerId . '_' . $storeId;
        if (!isset($this->indexByCode[$indexCode])) {
            $this->indexByCode[$indexCode] = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        }

        return $this->indexByCode[$indexCode];
    }

    /**
     * Retrieve mapped attributes from class.
     * Original method.
     *
     * @param string $indexName
     * @return array
     */
    private function getMappedAttributes(string $indexName): array
    {
        if (empty($this->mappedAttributes[$indexName])) {
            $mappedAttributes = $this->client->getMapping(['index' => $indexName]);
            $pathField = $this->arrayManager->findPath('properties', $mappedAttributes);
            $this->mappedAttributes[$indexName] = $this->arrayManager->get($pathField, $mappedAttributes, []);
        }

        return $this->mappedAttributes[$indexName];
    }

    /**
     * Set mapped attributes to class.
     * Original method.
     *
     * @param string $indexName
     * @param array $mappedAttributes
     * @return $this
     */
    private function setMappedAttributes(string $indexName, array $mappedAttributes): self
    {
        foreach ($mappedAttributes as $attributeCode => $attributeParams) {
            $this->mappedAttributes[$indexName][$attributeCode] = $attributeParams;
        }

        return $this;
    }

    /**
     * @OVERRIDE
     * Create new index with mapping.
     *
     * @param int $storeId
     * @param string $indexName
     * @param string $mappedIndexerId
     * @return $this
     */
    protected function prepareIndex($storeId, $indexName, $mappedIndexerId): self
    {
        $this->indexBuilder->setStoreId($storeId);

        // >>> UPD START
        $settings = [];
        // >>> UPD END

        $allAttributeTypes = $this->fieldMapper->getAllAttributesTypes(
            [
                'entityType' => $mappedIndexerId,
                // Use store id instead of website id from context for save existing fields mapping.
                // In future websiteId will be eliminated due to index stored per store
                'websiteId' => $storeId,
                // this parameter is introduced to replace 'websiteId' which name does not reflect
                // the value assigned to it
                'storeId' => $storeId
            ]
        );
        $settings['index']['mapping']['total_fields']['limit'] = $this->getMappingTotalFieldsLimit($allAttributeTypes);
        $this->client->createIndex($indexName, ['settings' => $settings]);
        $this->client->addFieldsMapping(
            $allAttributeTypes,
            $indexName,
            $this->clientConfig->getEntityType()
        );
        $this->preparedIndex[$storeId] = $indexName;

        return $this;
    }

    /**
     * Get total fields limit for mapping.
     * Original method.
     * @param array $allAttributeTypes
     * @return int
     */
    private function getMappingTotalFieldsLimit(array $allAttributeTypes): int
    {
        $count = count($allAttributeTypes);

        foreach ($allAttributeTypes as $attributeType) {
            if (isset($attributeType['fields'])) {
                $count += count($attributeType['fields']);
            }
        }

        return $count + self::MAPPING_TOTAL_FIELDS_BUFFER_LIMIT;
    }
}
