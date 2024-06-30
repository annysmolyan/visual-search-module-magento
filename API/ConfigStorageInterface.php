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

/**
 * @api
 * Interface ConfigStorageInterface
 * Manage admin config
 * @package BelSmol\VisualSearch\API
 */
interface ConfigStorageInterface
{
    /**
     * @return bool
     */
    public function isModuleEnabled(): bool;

    /**
     * @return bool
     */
    public function isElasticVersionCorrect(): bool;

    /**
     * @return bool
     */
    public function allCategoriesIncluded(): bool;

    /**
     * @return array
     */
    public function getIncludedCategoriesIds(): array;

    /**
     * @return array
     */
    public function getExcludedCategoriesIds(): array;

    /**
     * @return bool
     */
    public function isEnabledCategorySelection(): bool;

    /**
     * @return int
     */
    public function getSearchItemsCount(): int;

    /**
     * @return float
     */
    public function getMinRelevanceScope(): float;

    /**
     * @return string
     */
    public function getVectorUpdateMode(): string;

    /**
     * @return string
     */
    public function getAiServerDomain(): string;

    /**
     * @return int
     */
    public function getSearchDataBatchSize(): int;

    /**
     * @return bool
     */
    public function canRemoveTmpSearchImagesByCron(): bool;

    /**
     * @return int
     */
    public function getSavedTempImagesCount(): int;

    /**
     * @return bool
     */
    public function canCleanQueueTableByCron(): bool;

    /**
     * @return int
     */
    public function getQueueRowsSavedCount(): int;

    /**
     * @return bool
     */
    public function canCleanSearchRequestTableByCron(): bool;

    /**
     * @return int
     */
    public function getSearchRequestRowsSavedCount(): int;

    /**
     * @return bool
     */
    public function canCleanVectorCsvFilesByCron(): bool;

    /**
     * @return int
     */
    public function getVectorCsvFilesSavedCount(): int;

    /**
     * @return string
     */
    public function getCnnModel(): string;

    /**
     * @return string
     */
    public function getElasticsearchHost(): string;

    /**
     * @return string
     */
    public function getElasticsearchPort(): string;

    /**
     * @return string
     */
    public function getElasticsearchIndexPrefix(): string;

    /**
     * @return int
     */
    public function getApiProductPageSize(): int;
}
