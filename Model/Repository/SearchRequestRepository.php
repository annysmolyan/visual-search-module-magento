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

use BelSmol\VisualSearch\API\Data\SearchRequestInterface;
use BelSmol\VisualSearch\API\Data\SearchRequestInterfaceFactory;
use BelSmol\VisualSearch\API\Data\SearchRequestSearchResultInterface;
use BelSmol\VisualSearch\API\Data\SearchRequestSearchResultInterfaceFactory;
use BelSmol\VisualSearch\API\SearchRequestRepositoryInterface;
use BelSmol\VisualSearch\Model\ResourceModel\SearchRequest as ResourceModel;
use BelSmol\VisualSearch\Model\ResourceModel\SearchRequest\CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SearchRequestRepository
 * @package BelSmol\VisualSearch\Model\Repository
 */
class SearchRequestRepository implements SearchRequestRepositoryInterface
{
    /**
     * @param ResourceModel $resourceModel
     * @param SearchRequestInterfaceFactory $searchRequestFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     * @param SearchRequestSearchResultInterfaceFactory $searchResultFactory
     */
    public function __construct(
        protected ResourceModel $resourceModel,
        protected SearchRequestInterfaceFactory $searchRequestFactory,
        protected CollectionProcessorInterface $collectionProcessor,
        protected CollectionFactory $collectionFactory,
        protected SearchRequestSearchResultInterfaceFactory $searchResultFactory
    ) {}

    /**
     * @param int $id
     * @return SearchRequestInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): SearchRequestInterface
    {
        $term = $this->searchRequestFactory->create();
        $this->resourceModel->load($term, $id);

        if (!$term->getId()) {
            throw new NoSuchEntityException(__('Unable to find Search Request with ID %1', $id));
        }

        return $term;
    }

    /**
     * @param SearchRequestInterface $searchRequest
     * @return SearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function save(SearchRequestInterface $searchRequest): SearchRequestInterface
    {
        try {
            $this->resourceModel->save($searchRequest);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save Search Request: %1', $exception->getMessage()),
                $exception
            );
        }

        return $searchRequest;
    }

    /**
     * @param SearchRequestInterface $searchRequest
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(SearchRequestInterface $searchRequest): void
    {
        try {
            $this->resourceModel->delete($searchRequest);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete Search Request : %1',
                $exception->getMessage()
            ));
        }
    }

    /**
     * @param int $id
     * @return void
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): void
    {
        $term = $this->getById($id);
        $this->delete($term);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchRequestSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchRequestSearchResultInterface
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
