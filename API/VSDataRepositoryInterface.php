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

use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\API\Data\VSSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 * Interface VSDataRepositoryInterface
 * @package BelSmol\VisualSearch\API
 */
interface VSDataRepositoryInterface
{
    /**
     * @param int $id
     * @return VSDataInterface
     */
    public function getById(int $id): VSDataInterface;

    /**
     * @param VSDataInterface $vsData
     */
    public function save(VSDataInterface $vsData): VSDataInterface;

    /**
     * @param VSDataInterface $vsData
     * @return void
     */
    public function delete(VSDataInterface $vsData): void;

    /**
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VSSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VSSearchResultInterface;

    /**
     * @param int $productId
     * @param int $storeId
     * @return VSDataInterface
     */
    public function getByProductIdAndStore(int $productId, int $storeId): VSDataInterface;
}
