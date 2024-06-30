<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigStorage
 * Manage admin config
 * @package BelSmol\VisualSearch\Model
 */
class ConfigStorage implements ConfigStorageInterface
{
    const CONFIG_ENABLED = 'visual_search/general/enabled';
    const CONFIG_ALL_CATEGORIES_INCLUDED = 'visual_search/search_settings/include_all';
    const CONFIG_INCLUDED_CATEGORIES = 'visual_search/search_settings/included_categories';
    const CONFIG_EXCLUDED_CATEGORIES = 'visual_search/search_settings/excluded_categories';
    const CONFIG_ENABLE_CATEGORY_SELECTION = 'visual_search/search_settings/enable_category_selection';
    const CONFIG_SEARCH_ITEMS_COUNT = 'visual_search/search_settings/search_items_count';
    const CONFIG_MIN_RELEVANCE_SCOPE = 'visual_search/search_settings/min_relevance_scope';
    const CONFIG_SEARCH_ENGINE_CONFIG = 'catalog/search/engine';
    const CONFIG_REMOVE_TMP_IMG_BY_CRON = 'visual_search/cleaning_settings/remove_tmp_img_by_cron';
    const CONFIG_TMP_IMG_SAVED_COUNT = 'visual_search/cleaning_settings/tmp_img_saved_count';
    const CONFIG_CLEAN_QUEUE_BY_CRON = 'visual_search/cleaning_settings/clean_queue_by_cron';
    const CONFIG_QUEUE_ROWS_SAVED_COUNT = 'visual_search/cleaning_settings/queue_rows_saved_count';
    const CONFIG_CLEAN_SEARCH_REQUEST_TABLE_BY_CRON = 'visual_search/cleaning_settings/clean_search_request_by_cron';
    const CONFIG_SEARCH_REQUEST_SAVED_COUNT = 'visual_search/cleaning_settings/search_request_rows_saved_count';
    const CONFIG_CLEAN_VECTOR_CSV_BY_CRON = 'visual_search/cleaning_settings/clean_search_request_by_cron';
    const CONFIG_VECTOR_CSV_SAVED_COUNT = 'visual_search/cleaning_settings/vector_csv_saved_count';
    const CONFIG_VECTOR_UPD_MODE = 'visual_search/vector_settings/upd_mode';
    const CONFIG_VECTOR_BATCH_SIZE = 'visual_search/vector_settings/batch_size';
    const CONFIG_AI_SERVER_DOMAIN = 'visual_search/ai_settings/domain';
    const CONFIG_AI_SERVER_CNN_MODEL = 'visual_search/ai_settings/cnn_model';
    const CONFIG_ELASTICSEARCH_HOST = 'catalog/search/elasticsearch8_server_hostname';
    const CONFIG_ELASTICSEARCH_PORT = 'catalog/search/elasticsearch8_server_port';
    const CONFIG_ELASTICSEARCH_INDEX_PREFIX = 'catalog/search/elasticsearch8_index_prefix';
    const CONFIG_API_PRODUCT_PAGE_SIZE = 'catalog/search/api_product_page_size';

    protected const ELASTIC_8_VERSION = 'elasticsearch8';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(protected ScopeConfigInterface $scopeConfig)
    {}

    /**
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isElasticVersionCorrect(): bool
    {
        return $this->scopeConfig->getValue(self::CONFIG_SEARCH_ENGINE_CONFIG) == self::ELASTIC_8_VERSION;
    }

    /**
     * @return bool
     */
    public function allCategoriesIncluded(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_ALL_CATEGORIES_INCLUDED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getIncludedCategoriesIds(): array
    {
        $configValue = (string)$this->scopeConfig->getValue(
            self::CONFIG_INCLUDED_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );

        return $configValue ? explode(',', $configValue) : [];
    }

    /**
     * @return array
     */
    public function getExcludedCategoriesIds(): array
    {
        $configValue = (string)$this->scopeConfig->getValue(
            self::CONFIG_EXCLUDED_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );

        return $configValue ? explode(',', $configValue) : [];
    }

    /**
     * @return bool
     */
    public function isEnabledCategorySelection(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_ENABLE_CATEGORY_SELECTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getSearchItemsCount(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_SEARCH_ITEMS_COUNT
        );
    }

    /**
     * @return float
     */
    public function getMinRelevanceScope(): float
    {
        return (float)$this->scopeConfig->getValue(
            self::CONFIG_MIN_RELEVANCE_SCOPE
        );
    }

    /**
     * @return string
     */
    public function getVectorUpdateMode(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_VECTOR_UPD_MODE
        );
    }

    /**
     * @return string
     */
    public function getAiServerDomain(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_AI_SERVER_DOMAIN
        );
    }

    /**
     * @return int
     */
    public function getSearchDataBatchSize(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_VECTOR_BATCH_SIZE
        );
    }

    /**
     * @return bool
     */
    public function canRemoveTmpSearchImagesByCron(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_REMOVE_TMP_IMG_BY_CRON
        );
    }

    /**
     * @return int
     */
    public function getSavedTempImagesCount(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_TMP_IMG_SAVED_COUNT
        );
    }

    /**
     * @return bool
     */
    public function canCleanQueueTableByCron(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_CLEAN_QUEUE_BY_CRON
        );
    }

    /**
     * @return int
     */
    public function getQueueRowsSavedCount(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_QUEUE_ROWS_SAVED_COUNT
        );
    }

    /**
     * @return bool
     */
    public function canCleanSearchRequestTableByCron(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_CLEAN_SEARCH_REQUEST_TABLE_BY_CRON
        );
    }

    /**
     * @return int
     */
    public function getSearchRequestRowsSavedCount(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_SEARCH_REQUEST_SAVED_COUNT
        );
    }

    /**
     * @return bool
     */
    public function canCleanVectorCsvFilesByCron(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_CLEAN_VECTOR_CSV_BY_CRON
        );
    }

    /**
     * @return int
     */
    public function getVectorCsvFilesSavedCount(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_VECTOR_CSV_SAVED_COUNT
        );
    }

    /**
     * @return string
     */
    public function getCnnModel(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_AI_SERVER_CNN_MODEL
        );
    }

    /**
     * @return string
     */
    public function getElasticsearchHost(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_ELASTICSEARCH_HOST
        );
    }

    /**
     * @return string
     */
    public function getElasticsearchPort(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_ELASTICSEARCH_PORT
        );
    }

    /**
     * @return string
     */
    public function getElasticsearchIndexPrefix(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_ELASTICSEARCH_INDEX_PREFIX
        );
    }

    /**
     * @return int
     */
    public function getApiProductPageSize(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_API_PRODUCT_PAGE_SIZE
        );
    }
}
