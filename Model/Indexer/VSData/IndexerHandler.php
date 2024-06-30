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

use BelSmol\VisualSearch\Model\Indexer\VSData\ElasticSearchAdapter as VisualSearchElasticSearchAdapter;
use Exception;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler as ElasticsearchIndexerHandler;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Traversable;

/**
 * @OVERRIDE
 * Class IndexerHandler
 * Manage index creation and so on here
 * @package BelSmol\VisualSearch\Model\Indexer\VSData
 */
class IndexerHandler extends ElasticsearchIndexerHandler
{
    protected const INDEXER_ID = 'indexer_id';

    /**
     * @OVERRIDE
     * Replace magento adapter with custom adapter because of missed indexerId parameter
     * if you want to change batch size, then use di.xml and inject $batchSize value
     *
     * @param VisualSearchElasticSearchAdapter $adapter
     * @param IndexStructure $indexStructure
     * @param ElasticsearchAdapter $originalAdapter
     * @param IndexNameResolver $indexNameResolver
     * @param Batch $batch
     * @param ScopeResolverInterface $scopeResolver
     * @param array $data
     * @param int $batchSize
     * @param DeploymentConfig|null $deploymentConfig
     * @param CacheContext|null $cacheContext
     * @param Processor|null $processor
     */
    public function __construct(
        private VisualSearchElasticSearchAdapter $adapter,
        private IndexStructure $indexStructure,
        ElasticsearchAdapter $originalAdapter,
        private IndexNameResolver $indexNameResolver,
        private Batch $batch,
        private ScopeResolverInterface $scopeResolver,
        private array $data = [],
        private int $batchSize = ElasticsearchIndexerHandler::DEFAULT_BATCH_SIZE,
        private ?DeploymentConfig $deploymentConfig = null,
        private ?CacheContext $cacheContext = null,
        private ?Processor $processor = null
    ){
        parent::__construct(
            $indexStructure,
            $originalAdapter,
            $indexNameResolver,
            $batch,
            $scopeResolver,
            $data,
            $batchSize,
            $deploymentConfig,
            $cacheContext,
            $processor
        );
    }

    /**
     * @OVERRIDE
     * When magento runs $this->adapter->prepareDocsPerStore()
     * then it doesn't send current indexer name to the adapter.
     * In this case indexer takes data for catalog_search index instead of visual_search_data index
     *
     * @param $dimensions
     * @param Traversable $documents
     * @return IndexerInterface
     * @throws FileSystemException
     * @throws RuntimeException
     * @throws Exception
     */
    public function saveIndex($dimensions, Traversable $documents): IndexerInterface
    {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();

        //>>> UPD START:
        $this->adapter->setIndexerId($this->getIndexerId());

        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $scopeId);
            $this->adapter->addDocs($docs, $scopeId, $this->getIndexerId());
        }
        //>>> UPD END

        $this->adapter->updateAlias($scopeId, $this->getIndexerId());

        return $this;
    }

    /**
     * Returns indexer id.
     * copied from parent class, because it's private
     * @return string
     */
    private function getIndexerId(): string
    {
        return (string)$this->indexNameResolver->getIndexMapping($this->data[self::INDEXER_ID]);
    }
}
