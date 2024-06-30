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

/**
 * @api
 * Interface SearchRequestManagerInterface
 * Manage visual search POST request
 * @package BelSmol\VisualSearch\API
 */
interface SearchRequestManagerInterface
{
    /**
     * @param string $imagePath
     * @param int $storeId
     * @param array $categories
     * @return SearchRequestInterface
     */
    public function createSearchRequest(
        string $imagePath,
        int $storeId,
        array $categories = []
    ): SearchRequestInterface;

    /**
     * @param string $value
     * @return SearchRequestInterface|null
     */
    public function getBySearchTermValue(string $value): ?SearchRequestInterface;
}
