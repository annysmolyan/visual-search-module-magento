<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Queue;

use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\API\QueueTaskRepositoryInterface;
use BelSmol\VisualSearch\API\VSDataManagerInterface;
use BelSmol\VisualSearch\Model\Indexer\VSData\Indexer;
use BelSmol\VisualSearch\Model\ResourceModel\VSData\CollectionFactory;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Class ImageVectorChangeConsumer
 * Process queue task here
 * @package BelSmol\VisualSearch\Model\Queue
 */
class ImageVectorChangeConsumer
{
    /**
     * @param VSDataManagerInterface $vsDataManager
     * @param QueueTaskRepositoryInterface $queueTaskRepository
     * @param IndexerRegistry $indexerRegistry
     * @param CollectionFactory $vsDataCollectionFactory
     */
    public function __construct(
        protected VSDataManagerInterface $vsDataManager,
        protected QueueTaskRepositoryInterface $queueTaskRepository,
        protected IndexerRegistry $indexerRegistry,
        protected CollectionFactory $vsDataCollectionFactory
    ) {}

    /**
     * Process task here
     * @param QueueTaskInterface $task
     * @return void
     */
    public function processMessage(QueueTaskInterface $task): void
    {
        try {
            $task->setStatus(QueueTaskInterface::STATUS_IN_PROGRESS);
            $this->queueTaskRepository->save($task);

            $updatedRowIds = $this->vsDataManager->updateVisualSearchData($task->getSkus());
            $vsDataIndexer = $this->indexerRegistry->get(Indexer::INDEXER_ID);

            if ($updatedRowIds && !$vsDataIndexer->isScheduled()) { //reindex only if "update on save"
                $task->setStatus(QueueTaskInterface::STATUS_REINDEX);
                $this->queueTaskRepository->save($task);
                $vsDataIndexer->reindexList($updatedRowIds);
            }

            $message = $this->getLogMessage($updatedRowIds);

            $task->setLogMessage($message);
            $task->setStatus(QueueTaskInterface::STATUS_SUCCESS);
        } catch (Exception $exception) {
            $task->setStatus(QueueTaskInterface::STATUS_ERROR);
            $task->setLogMessage($exception->getMessage());
        }

        $this->queueTaskRepository->save($task);
    }

    /**
     * @param array $updatedRowIds
     * @return string
     */
    private function getLogMessage(array $updatedRowIds): string
    {
        return $updatedRowIds
            ? "Updated product skus: " . implode(',', $this->getUpdatedProductSkus($updatedRowIds))
            : "Nothing has been updated";
    }

    /**
     * @param array $vsDataEntityIds
     * @return array
     */
    private function getUpdatedProductSkus(array $vsDataEntityIds): array
    {
        $collection = $this->vsDataCollectionFactory->create();
        $collection->addFieldToSelect(VSDataInterface::FIELD_PRODUCT_ID);
        $collection->getSelect()
            ->join(
                ['cpe' => 'catalog_product_entity'],
                'cpe.entity_id = main_table.product_id',
                [Product::SKU]
            )
            ->where('main_table.entity_id IN (?)', $vsDataEntityIds);

        return $collection->getColumnValues(Product::SKU);
    }
}

