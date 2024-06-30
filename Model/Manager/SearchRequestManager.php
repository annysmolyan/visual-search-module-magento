<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Manager;

use BelSmol\VisualSearch\API\Data\SearchRequestInterface;
use BelSmol\VisualSearch\API\Data\SearchRequestInterfaceFactory;
use BelSmol\VisualSearch\API\SearchRequestManagerInterface;
use BelSmol\VisualSearch\API\SearchRequestRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

/**
 * Class SearchRequestManager
 * Manage visual search POST request
 * @package BelSmol\VisualSearch\Model\Manager
 */
class SearchRequestManager implements SearchRequestManagerInterface
{
    /**
     * @param SearchRequestInterfaceFactory $searchRequestFactory
     * @param SearchRequestRepositoryInterface $searchTermRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        protected SearchRequestInterfaceFactory $searchRequestFactory,
        protected SearchRequestRepositoryInterface $searchTermRepository,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ){}

    /**
     * @param string $imagePath
     * @param int $storeId
     * @param array $categories
     * @return SearchRequestInterface
     */
    public function createSearchRequest(string $imagePath, int $storeId, array $categories = []): SearchRequestInterface
    {
        $searchRequest = $this->searchRequestFactory->create();
        $searchRequest->setStoreId($storeId);
        $searchRequest->setImagePath($imagePath);
        $searchRequest->setSearchParamValue($this->generateSearchParamValue());

        if ($categories) {
            $searchRequest->setCategories($categories);
        }

        return $this->searchTermRepository->save($searchRequest);
    }

    /**
     * @return string
     */
    protected function generateSearchParamValue(): string
    {
        $timestamp = time(); // Current timestamp
        $randomString = substr(
            str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5
        ); // Random 5 characters

        return $randomString . $timestamp;
    }

    /**
     * @param string $value
     * @return SearchRequestInterface|null
     */
    public function getBySearchTermValue(string $value): ?SearchRequestInterface
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter(SearchRequestInterface::FIELD_SEARCH_PARAM_VALUE, $value);

        $criteria = $searchCriteriaBuilder->create();
        $result = $this->searchTermRepository->getList($criteria)->getItems();

        return $result ? reset($result) : null;
    }
}
