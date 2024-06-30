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
 * Interface VSDataInterface
 * Entity to keep visual search data in db
 * @package BelSmol\VisualSearch\API\Data
 */
interface VSDataInterface
{
    const FIELD_ENTITY_ID = "entity_id";
    const FIELD_PRODUCT_ID = "product_id";
    const FIELD_STORE_ID = "store_id";
    const FIELD_VECTOR = "vector";
    const FIELD_PATH = "path";

    const TABLE_NAME = "visual_search_data";

    /**
     * No return type to fit Abstract model method
     * @return string|int
     */
    public function getId();

    /**
     * @param int $productId
     * @return void
     */
    public function setProductId(int $productId): void;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void;

    /**
     * @return array
     */
    public function getVector(): array;

    /**
     * @param array $vector
     * @return void
     */
    public function setVector(array $vector): void;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void;
}
