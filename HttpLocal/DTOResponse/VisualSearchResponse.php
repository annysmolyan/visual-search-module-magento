<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\HttpLocal\DTOResponse;

use BelSmol\VisualSearch\API\Data\HttpLocalDTOResponseVisualSearchInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class VisualSearchResponse
 * Is used as visual search API response object
 * @package BelSmol\VisualSearch\HttpLocal\DTOResponse
 */
class VisualSearchResponse implements HttpLocalDTOResponseVisualSearchInterface
{
    /**
     * @param int $totalCount
     * @param int $totalPages
     * @param int $currentPage
     * @param array $products
     */
    public function __construct(
        protected int $totalCount,
        protected int $totalPages,
        protected int $currentPage,
        protected array $products
    ){}

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return ProductInterface[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
