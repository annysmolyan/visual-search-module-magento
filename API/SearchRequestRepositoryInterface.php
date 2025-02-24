<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */
declare(strict_types=1);

namespace BelSmol\VisualSearch\API;

use BelSmol\VisualSearch\API\Data\SearchRequestInterface;
use BelSmol\VisualSearch\API\Data\SearchRequestSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 * Interface SearchRequestRepositoryInterface
 * @package BelSmol\VisualSearch\API
 */
interface SearchRequestRepositoryInterface
{
    /**
     * @param int $id
     * @return SearchRequestInterface
     */
    public function getById(int $id): SearchRequestInterface;

    /**
     * @param SearchRequestInterface $searchRequest
     */
    public function save(SearchRequestInterface $searchRequest): SearchRequestInterface;

    /**
     * @param SearchRequestInterface $searchRequest
     * @return void
     */
    public function delete(SearchRequestInterface $searchRequest): void;

    /**
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchRequestSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchRequestSearchResultInterface;
}
