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
 * Interface SearchServiceInterface
 * Is used for external usage
 * e.g. in API controllers or 3-d party modules
 * @package BelSmol\VisualSearch\API
 */
interface SearchServiceInterface
{
    /**
     * Search by encoded base 64 image
     * @param string $base64ImgData
     * @param array $categoryIds
     * @return ProductCollection
     */
    public function search(string $base64ImgData, array $categoryIds = []): ProductCollection;
}
