<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Repository;

use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\API\Data\QueueTaskInterfaceFactory;
use BelSmol\VisualSearch\API\Data\QueueTaskSearchResultInterface;
use BelSmol\VisualSearch\API\Data\QueueTaskSearchResultInterfaceFactory;
use BelSmol\VisualSearch\API\QueueTaskRepositoryInterface;
use BelSmol\VisualSearch\Model\ResourceModel\QueueTask as ResourceModel;
use BelSmol\VisualSearch\Model\ResourceModel\QueueTask\CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class QueueTaskRepository
 * @package BelSmol\VisualSearch\Model\Repository
 */
class QueueTaskRepository implements QueueTaskRepositoryInterface
{
    /**
     * @param QueueTaskSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param ResourceModel $resourceModel
     * @param QueueTaskInterfaceFactory $taskFactory
     */
    public function __construct(
        protected QueueTaskSearchResultInterfaceFactory $searchResultFactory,
        protected CollectionProcessorInterface $collectionProcessor,
        protected CollectionFactory $collectionFactory,
        protected ResourceModel $resourceModel,
        protected QueueTaskInterfaceFactory $taskFactory
    ) {}

    /**
     * @param int $id
     * @return QueueTaskInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): QueueTaskInterface
    {
        $task = $this->taskFactory->create();
        $this->resourceModel->load($task, $id);

        if (!$task->getId()) {
            throw new NoSuchEntityException(__('Unable to find Queue Task with ID %1', $id));
        }

        return $task;
    }

    /**
     * @param QueueTaskInterface $task
     * @return QueueTaskInterface
     * @throws CouldNotSaveException
     */
    public function save(QueueTaskInterface $task): QueueTaskInterface
    {
        try {
            $this->resourceModel->save($task);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save Queue Task: %1', $exception->getMessage()),
                $exception
            );
        }

        return $task;
    }

    /**
     * @param QueueTaskInterface $task
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(QueueTaskInterface $task): void
    {
        try {
            $this->resourceModel->delete($task);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete Queue Task: %1',
                $exception->getMessage()
            ));
        }
    }

    /**
     * @param int $id
     * @return void
     * @throws CouldNotDeleteException|NoSuchEntityException
     */
    public function deleteById(int $id): void
    {
        $task = $this->getById($id);
        $this->delete($task);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return QueueTaskSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): QueueTaskSearchResultInterface
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
