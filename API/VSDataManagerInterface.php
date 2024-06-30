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

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * @api
 * Interface VSDataManagerInterface
 * Manage visual search data table
 * @package BelSmol\VisualSearch\API
 */
interface VSDataManagerInterface
{
    /**
     * @return void
     */
    public function createFullCatalogVisualSearchData(): void;

    /**
     * @param array $skus
     * @return array
     */
    public function updateVisualSearchData(array $skus = []): array;

    /**
     * @param int $storeId
     * @return array
     */
    public function getProductIdsToBeRemovedFromIndex(int $storeId): array;

    /**
     * @param int $storeId
     * @param array $skus
     * @return ProductCollection
     */
    public function getSearchableProductCollection(int $storeId, array $skus = []): ProductCollection;

    /**
     * @param int $storeId
     * @param array $skus
     * @return ProductCollection
     */
    public function getOutdatedProductCollection(int $storeId, array $skus = []): ProductCollection;
}
