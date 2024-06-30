<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Manager;

use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\API\Data\SearchRequestInterface;
use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\API\DBManagerInterface;
use BelSmol\VisualSearch\Model\ResourceModel\VSData as ResourceVSData;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class DBManager
 * This class is used for managing visual_search_data table creation.
 * Use temp table to build data and then copy it to the original table
 * @package BelSmol\VisualSearch\Model\Manager
 */
class DBManager implements DBManagerInterface
{
    public const VS_DATA_TMP_TABLE = "visual_search_data_tmp";
    public const EAV_ATTRIBUTE_TABLE = "eav_attribute";

    /**
     * @param ResourceConnection $resourceConnection
     * @param ResourceVSData $resourceVSData
     */
    public function __construct(
        private ResourceConnection $resourceConnection,
        protected ResourceVSData $resourceVSData
    ) {}

    /**
     * Get connection with database
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Truncate temp table
     * @return void
     */
    public function truncateVSDataTmpTable(): void
    {
        $tableName = $this->getTableName(self::VS_DATA_TMP_TABLE);
        $connection = $this->getConnection();

        if ($connection->isTableExists($tableName)) {
            $connection->truncateTable($tableName);
        }
    }

    /**
     * @return void
     */
    public function deleteVSDataTmpTable(): void
    {
        $tableName = $this->getTableName(self::VS_DATA_TMP_TABLE);
        $connection = $this->getConnection();

        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }
    }

    /**
     * Create temp table
     * @return void
     */
    public function createVSDataTmpTable(): void
    {
        $connection = $this->getConnection();

        if (!$connection->isTableExists(self::VS_DATA_TMP_TABLE)) {
            $tempTableName = $this->getTableName(self::VS_DATA_TMP_TABLE);
            $mainTableName = $this->getTableName(VSDataInterface::TABLE_NAME);

            $connection->query(
                sprintf("CREATE TABLE %s LIKE %s;", $tempTableName, $mainTableName)
            );
        }
    }

    /**
     * Truncate original table
     * @return void
     * @throws LocalizedException
     */
    public function truncateVSDataTable(): void
    {
        $this->resourceVSData->truncateTable();
    }

    /**
     * Insert content from tmp to original table
     * @return void
     */
    public function copyContentFromVSDataTmpTable(): void
    {
        $tempTableName = $this->getTableName(self::VS_DATA_TMP_TABLE);
        $mainTableName = $this->getTableName(VSDataInterface::TABLE_NAME);

        $select = $this->getConnection()
            ->select()
            ->from($tempTableName);

        $this->getConnection()->query($this->getConnection()->insertFromSelect($select, $mainTableName));
    }

    /**
     * @param array $data
     * @return void
     */
    public function insertIntoVSDataTmpTable(array $data): void
    {
        $tableName = $this->getTableName(self::VS_DATA_TMP_TABLE);

        $this->getConnection()->insertArray(
            $tableName,
            [
                VSDataInterface::FIELD_PRODUCT_ID,
                VSDataInterface::FIELD_STORE_ID,
                VSDataInterface::FIELD_VECTOR,
                VSDataInterface::FIELD_PATH
            ],
            $data
        );
    }

    /**
     * @param array $data
     * @return void
     */
    public function insertIntoVSDataTable(array $data): void
    {
        $tableName = $this->getTableName(VSDataInterface::TABLE_NAME);

        $this->getConnection()->insertArray(
            $tableName,
            [
                VSDataInterface::FIELD_PRODUCT_ID,
                VSDataInterface::FIELD_STORE_ID,
                VSDataInterface::FIELD_VECTOR,
                VSDataInterface::FIELD_PATH
            ],
            $data
        );
    }

    /**
     * Get table name with prefix by original name
     * @param string $originalTableName
     * @return string
     */
    public function getTableName(string $originalTableName): string
    {
        return $this->getConnection()->getTableName($originalTableName);
    }

    /**
     * @param string $attributeCode
     * @return int
     * @throws NoSuchEntityException
     */
    public function getEavAttributeId(string $attributeCode): int
    {
        $connection = $this->getConnection();
        $eavTableName = $this->getTableName(self::EAV_ATTRIBUTE_TABLE);

        $attributeId = $connection->select()
            ->from(['eav_attribute' => $eavTableName], ['attribute_id'])
            ->where(
                sprintf("attribute_code = '%s'", $attributeCode)
            )->query()->fetchColumn(0);

        if (!$attributeId) {
            throw new NoSuchEntityException(
                __(sprintf("Attribute id doesn't exist for code '%s'", $attributeCode))
            );
        }

        return (int) $attributeId;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @return void
     * @throws Exception
     */
    public function removeFromVsDataTable(int $storeId, array $productIds): void
    {
        $tableName = $this->getTableName(VSDataInterface::TABLE_NAME);
        $connection = $this->getConnection();

        if (!$connection->isTableExists($tableName)) {
            throw new Exception(sprintf("Can't delete rows from '%s' table. It doesn't exist", $tableName));
        }

        $select = $connection->select()
            ->from($tableName)
            ->where(
                sprintf("%s = %s and %s in ('%s')",
                    VSDataInterface::FIELD_STORE_ID,
                    $storeId,
                    VSDataInterface::FIELD_PRODUCT_ID,
                    implode("','", $productIds)
                )
            );

        $query = $connection->deleteFromSelect($select, $tableName);
        $connection->query($query);
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @return array
     * @throws Exception
     */
    public function getVsDataEntityIdListByProductIds(int $storeId, array $productIds): array
    {
        $tableName = $this->getTableName(VSDataInterface::TABLE_NAME);
        $connection = $this->getConnection();

        if (!$connection->isTableExists($tableName)) {
            throw new Exception(sprintf("Table '%s' doesn't exist", $tableName));
        }

        $select = $connection->select()
            ->from($tableName, [VSDataInterface::FIELD_ENTITY_ID])
            ->where(sprintf("%s = %s and %s in ('%s')",
                VSDataInterface::FIELD_STORE_ID,
                $storeId,
                VSDataInterface::FIELD_PRODUCT_ID,
                implode("','", $productIds)
            ));

        return $connection->fetchCol($select);
    }

    /**
     * @param int $limit
     * @return void
     */
    public function cleanQueueTable(int $limit = 0): void
    {
        $this->cleanTable(QueueTaskInterface::TABLE_NAME, $limit);
    }

    /**
     * @param int $limit
     * @return void
     */
    public function cleanVisualSearchRequestTable(int $limit = 0): void
    {
        $this->cleanTable(SearchRequestInterface::TABLE_NAME, $limit);
    }

    /**
     * @param string $tableName
     * @param int $limit
     * @return void
     */
    protected function cleanTable(string $tableName, int $limit = 0): void
    {
        $keepRows  = [];

        $connection = $this->getConnection();
        $tableName = $this->getTableName($tableName);

        if ($limit) {
            $keepRowsSelect = $connection
                ->select()
                ->from($tableName, 'entity_id')
                ->order('entity_id DESC')
                ->limit($limit);
            $keepRows = $connection->fetchCol($keepRowsSelect);
        }

        $condition = ($keepRows) ? implode(',', $keepRows) : "''";
        $connection->delete($tableName, QueueTaskInterface::FIELD_ENTITY_ID . " NOT IN (" . $condition . ")");
    }
}
