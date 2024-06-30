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

use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * Interface DBManagerInterface
 * Manage DB tables
 * @package BelSmol\VisualSearch\API
 */
interface DBManagerInterface
{
    /**
     * Truncate temp table
     * @return void
     */
    public function truncateVSDataTmpTable(): void;

    /**
     * @return void
     */
    public function deleteVSDataTmpTable(): void;

    /**
     * Create temp table
     * @return void
     */
    public function createVSDataTmpTable(): void;

    /**
     * Truncate original table
     * @return void
     * @throws LocalizedException
     */
    public function truncateVSDataTable(): void;

    /**
     * Insert content from tmp to original table
     * @return void
     */
    public function copyContentFromVSDataTmpTable(): void;

    /**
     * @param array $data
     * @return void
     */
    public function insertIntoVSDataTmpTable(array $data): void;

    /**
     * @param array $data
     * @return void
     */
    public function insertIntoVSDataTable(array $data): void;

    /**
     * Get table name with prefix by original name
     * @param string $originalTableName
     * @return string
     */
    public function getTableName(string $originalTableName): string;

    /**
     * @param string $attributeCode
     * @return int
     */
    public function getEavAttributeId(string $attributeCode): int;

    /**
     * Remove rows from visual_search_data table
     * @param int $storeId
     * @param array $productIds
     * @return void
     */
    public function removeFromVsDataTable(int $storeId, array $productIds): void;

    /**
     * @param int $storeId
     * @param array $productIds
     * @return array
     */
    public function getVsDataEntityIdListByProductIds(int $storeId, array $productIds): array;

    /**
     * @param int $savedRows
     * @return void
     */
    public function cleanQueueTable(int $savedRows = 0): void;

    /**
     * @param int $limit
     * @return void
     */
    public function cleanVisualSearchRequestTable(int $limit = 0): void;
}
