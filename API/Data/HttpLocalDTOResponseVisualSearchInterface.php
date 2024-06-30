<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */
declare(strict_types=1);

namespace BelSmol\VisualSearch\API\Data;

/**
 * @api
 * Interface HttpLocalDTOResponseVisualSearchInterface
 * Is used as visual search API response object
 * @package BelSmol\VisualSearch\API\Data
 */
interface HttpLocalDTOResponseVisualSearchInterface
{
    /**
     * @return int
     */
    public function getTotalCount(): int;

    /**
     * @return int
     */
    public function getTotalPages(): int;
    /**
     * @return int
     */
    public function getCurrentPage(): int;

    /**
     * Don't import this interface, will cause error in API response
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProducts(): array;
}
