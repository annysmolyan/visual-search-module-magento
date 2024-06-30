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

use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\Model\ResourceModel\VSData as ResourceModel;
use Magento\Catalog\Model\AbstractModel;

/**
 * Class VSData
 * The table of this model is used only for filling table with data.
 * The data is needed to be used inside elasticsearch.
 * Image vectors will be used for search.
 * @package BelSmol\VisualSearch\Model
 */
class VSData extends AbstractModel implements VSDataInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @param int $productId
     * @return void
     */
    public function setProductId(int $productId): void
    {
        $this->setData(self::FIELD_PRODUCT_ID, $productId);
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->getData(self::FIELD_PRODUCT_ID);
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
     * @return array
     */
    public function getVector(): array
    {
        $vector = trim((string)$this->getData(self::FIELD_VECTOR));

        return $vector ? json_decode($vector) : [];
    }

    /**
     * @param array $vector
     * @return void
     */
    public function setVector(array $vector): void
    {
        $this->setData(self::FIELD_VECTOR, json_encode($vector));
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return (string)$this->getData(self::FIELD_PATH);
    }

    /**
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->setData(self::FIELD_PATH, $path);
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            self::FIELD_ENTITY_ID => $this->getId(),
            self::FIELD_PRODUCT_ID => $this->getProductId(),
            self::FIELD_STORE_ID => $this->getStoreId(),
            self::FIELD_VECTOR => $this->getVector(),
            self::FIELD_PATH => $this->getPath(),
        ];
    }
}
