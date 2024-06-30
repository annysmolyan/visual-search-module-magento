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
 * Interface SearchRequestInterface
 * Is used to store POST request in DB
 * and used in visual search result page
 * @package BelSmol\VisualSearch\API\Data
 */
interface SearchRequestInterface
{
    const TABLE_NAME = 'visual_search_request';

    const FIELD_ENTITY_ID = "entity_id";
    const FIELD_SEARCH_PARAM_VALUE = "search_param_value";
    const FIELD_STORE_ID = "store_id";
    const FIELD_IMAGE_PATH = "image_path";
    const FIELD_CATEGORIES = "categories";

    /**
     * No return type to fit Abstract model method
     * @return string|int
     */
    public function getId();

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
     * @return string
     */
    public function getSearchParamValue(): string;

    /**
     * @param string $value
     * @return void
     */
    public function setSearchParamValue(string $value): void;

    /**
     * @return string
     */
    public function getImagePath(): string;

    /**
     * @param string $path
     * @return void
     */
    public function setImagePath(string $path): void;

    /**
     * @param array $categories
     * @return void
     */
    public function setCategories(array $categories): void;

    /**
     * @return array
     */
    public function getCategories(): array;
}
