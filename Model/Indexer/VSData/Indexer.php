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

use ArrayIterator;
use BelSmol\VisualSearch\API\ConfigStorageInterface;
use Exception;
use InvalidArgumentException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\Mview\ActionInterface as MViewActionInterface;
use Magento\Indexer\Model\ProcessManager;
use Magento\Store\Model\StoreDimensionProvider;
use Traversable;

/**
 * Class Indexer
 * Main indexer class. Execute indexer here
 * @package BelSmol\VisualSearch\Model\Indexer\VSData
 */
class Indexer implements IndexerActionInterface, MViewActionInterface, DimensionalIndexerInterface
{
    public const INDEXER_ID = 'visual_search_data';

    protected const SKIP_MESSAGE = 'Skip visual search reindex. Module disabled.';

    /**
     * Where:
     *
     * $data - indexer structure
     * $dimensionProvider - is StoreDimension injected in di.xml
     * $processManager - determines how many thread to use
     * $action - build data for indexer
     * $indexerHandlerFactory - create handler for index (manages index creation and so on)
     *
     * @param array $data
     * @param DimensionProviderInterface $dimensionProvider
     * @param ProcessManager $processManager
     * @param Action $action
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param ConfigStorageInterface $configStorage
     */
    public function __construct(
        private DimensionProviderInterface $dimensionProvider,
        private ProcessManager $processManager,
        private Action $action,
        protected IndexerHandlerFactory $indexerHandlerFactory,
        private ConfigStorageInterface $configStorage,
        protected array $data = [],
    ) {}

    /**
     * DON'T USE TYPE HINT HERE
     * Call When Update on Schedule
     * @param $ids
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function execute($ids): void
    {
        if (!$this->canReindex()) {
            echo self::SKIP_MESSAGE;
            return;
        }

        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimensions($dimension, new ArrayIterator($ids));
        }
    }

    /**
     * Call when reindex via command line
     * @return void
     */
    public function executeFull(): void
    {
        if (!$this->canReindex()) {
            echo self::SKIP_MESSAGE;
            return;
        }

        $userFunctions = [];

        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $userFunctions[] = function () use ($dimension) {
                $this->executeByDimensions($dimension);
            };
        }

        $this->processManager->execute($userFunctions);
    }

    /**
     * Call When partial indexation by id list
     * Works with a set of entity changed (e.g. mass action)
     * @param array $ids
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function executeList(array $ids): void
    {
        if (!$this->canReindex()) {
            echo self::SKIP_MESSAGE;
            return;
        }

        $this->execute($ids);
    }

    /**
     * DON'T USE TYPE HINT HERE
     * Call When partial indexation by specific id
     * @param $id
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function executeRow($id): void
    {
        if (!$this->canReindex()) {
            echo self::SKIP_MESSAGE;
            return;
        }

        $this->execute([$id]);
    }

    /**
     * Execute indexer by specified dimension.
     * Accept array of dimensions DTO that represent indexer dimension
     * store dimension is set in di.xml
     * @param array $dimensions
     * @param Traversable|null $entityIds
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     * @throws Exception
     */
    public function executeByDimensions(array $dimensions, Traversable $entityIds = null): void
    {
        if (!$this->canReindex()) {
            echo self::SKIP_MESSAGE;
            return;
        }

        if (!$this->configStorage->isElasticVersionCorrect()) {
            throw new Exception('Error: This indexer requires Elasticsearch >= 8.0');
        }

        if (count($dimensions) > 1 || !isset($dimensions[StoreDimensionProvider::DIMENSION_NAME])) {
            throw new InvalidArgumentException('Indexer "' . self::INDEXER_ID . '" support only Store dimension');
        }

        $storeId = (int)$dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue();

        if (!isset($this->data['indexer_id'])) {
            $this->data["indexer_id"] = self::INDEXER_ID;
        }

        $indexerHandler = $this->indexerHandlerFactory->create(['data' => $this->data]);

        if (null === $entityIds) {
            $indexerHandler->cleanIndex($dimensions);
            $indexerHandler->saveIndex($dimensions, $this->action->rebuildStoreIndex($storeId));
        } else {
            // internal implementation works only with array
            $entityIds = iterator_to_array($entityIds);

            if ($indexerHandler->isAvailable($dimensions)) {
                $indexerHandler->deleteIndex($dimensions, new ArrayIterator($entityIds));
                $indexerHandler->saveIndex($dimensions, $this->action->rebuildStoreIndex($storeId, $entityIds));
            }
        }
    }

    /**
     * @return bool
     */
    private function canReindex(): bool
    {
        return $this->configStorage->isModuleEnabled();
    }
}
