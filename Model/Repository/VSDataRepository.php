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

use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\API\Data\VSDataInterfaceFactory;
use BelSmol\VisualSearch\API\Data\VSSearchResultInterface;
use BelSmol\VisualSearch\API\Data\VSSearchResultInterfaceFactory;
use BelSmol\VisualSearch\API\VSDataRepositoryInterface;
use BelSmol\VisualSearch\Model\ResourceModel\VSData as ResourceModel;
use BelSmol\VisualSearch\Model\ResourceModel\VSData\CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class VSDataRepository
 * @package BelSmol\VisualSearch\Model\Repository
 */
class VSDataRepository implements VSDataRepositoryInterface
{
    /**
     * @param VSSearchResultInterfaceFactory $searchResultFactory
     * @param ResourceModel $resourceModel
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param VSDataInterfaceFactory $vsDataFactory
     */
    public function __construct(
        protected VSSearchResultInterfaceFactory $searchResultFactory,
        protected ResourceModel $resourceModel,
        protected CollectionProcessorInterface $collectionProcessor,
        protected CollectionFactory $collectionFactory,
        protected VSDataInterfaceFactory $vsDataFactory
    ) {}

    /**
     * @param int $id
     * @return VSDataInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): VSDataInterface
    {
        $vsData = $this->vsDataFactory->create();
        $this->resourceModel->load($vsData, $id);

        if (!$vsData->getId()) {
            throw new NoSuchEntityException(__('Unable to find VS Data with ID %1', $id));
        }

        return $vsData;
    }

    /**
     * @param VSDataInterface $vsData
     * @return VSDataInterface
     * @throws CouldNotSaveException
     */
    public function save(VSDataInterface $vsData): VSDataInterface
    {
        try {
            $this->resourceModel->save($vsData);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save VS Data: %1', $exception->getMessage()),
                $exception
            );
        }

        return $vsData;
    }

    /**
     * @param VSDataInterface $vsData
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(VSDataInterface $vsData): void
    {
        try {
            $this->resourceModel->delete($vsData);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete VS Data: %1',
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
        $vsData = $this->getById($id);
        $this->delete($vsData);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VSSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VSSearchResultInterface
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return VSDataInterface
     * @throws NoSuchEntityException
     */
    public function getByProductIdAndStore(int $productId, int $storeId): VSDataInterface
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter(VSDataInterface::FIELD_PRODUCT_ID, $productId)
            ->addFieldToFilter(VSDataInterface::FIELD_STORE_ID, $storeId);

        $item = $collection->getFirstItem();

        if (!$item->getId()) {
            throw new NoSuchEntityException(
                __('Unable to find VS Data with ID %1 and store %1', $productId, $storeId)
            );
        }

        return $item;
    }
}
