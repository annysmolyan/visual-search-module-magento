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

use BelSmol\VisualSearch\API\Data\SearchRequestInterface;
use BelSmol\VisualSearch\Model\ResourceModel\SearchRequest as ResourceModel;
use Magento\Catalog\Model\AbstractModel;

/**
 * Class SearchRequest
 * Is used to store POST request in DB
 * and used in visual search result page
 * @package BelSmol\VisualSearch\Model
 */
class SearchRequest extends AbstractModel implements SearchRequestInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(resourceModel: ResourceModel::class);
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return (int)$this->getData(self::FIELD_STORE_ID);
    }

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void
    {
        $this->setData(self::FIELD_STORE_ID, $storeId);
    }

    /**
     * @return string
     */
    public function getSearchParamValue(): string
    {
        return (string)$this->getData(self::FIELD_SEARCH_PARAM_VALUE);
    }

    /**
     * @param string $value
     * @return void
     */
    public function setSearchParamValue(string $value): void
    {
        $this->setData(self::FIELD_SEARCH_PARAM_VALUE, $value);
    }

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        return (string)$this->getData(self::FIELD_IMAGE_PATH);
    }

    /**
     * @param string $path
     * @return void
     */
    public function setImagePath(string $path): void
    {
        $this->setData(self::FIELD_IMAGE_PATH, $path);
    }

    /**
     * @param array $categories
     * @return void
     */
    public function setCategories(array $categories): void
    {
        if ($categories) {
            $this->setData(self::FIELD_CATEGORIES, json_encode($categories));
        }
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        $skus = [];
        $data = $this->getData(self::FIELD_CATEGORIES);

        if ($data) {
            $skus = json_decode($data);
        }

        return $skus;
    }
}
