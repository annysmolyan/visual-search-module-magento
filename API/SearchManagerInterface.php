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

use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * @api
 * Interface SearchManagerInterface
 * Main logic for search
 * @package BelSmol\VisualSearch\API
 */
interface SearchManagerInterface
{
    /**
     * @param string $imagePubMediaPath
     * @param array $categoryIds
     * @return ProductCollection
     */
    public function getSimilarProductsByImage(
        string $imagePubMediaPath,
        array  $categoryIds = []
    ): ProductCollection;

    /**
     * @return bool
     */
    public function allowUserSelectCategories(): bool;

    /**
     * @param array $selectedByUserCategoryIds
     * @param int|null $storeId
     * @return CategoryCollection
     */
    public function getSearchCategoryCollection(
        array $selectedByUserCategoryIds = [],
        int $storeId = null
    ): CategoryCollection;

    /**
     * @param array $selectedByUserCategoryIds
     * @param int|null $storeId
     * @param array $productIds
     * @return ProductCollection
     */
    public function getSearchProductCollection(
        array $selectedByUserCategoryIds,
        int $storeId = null,
        array $productIds = []
    ): ProductCollection;
}
