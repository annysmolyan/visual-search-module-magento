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

use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\Model\ResourceModel\QueueTask as ResourceModel;
use Magento\Catalog\Model\AbstractModel;
use Magento\Framework\Exception\InputException;

/**
 * Class QueueTask
 * This class is used for creating a task for queue
 * the data will be managed using this class and admin user can see what's going on with the queue
 * @package BelSmol\VisualSearch\Model
 */
class QueueTask extends AbstractModel implements QueueTaskInterface
{
    protected array $allowedStatuses = [
        self::STATUS_SUCCESS,
        self::STATUS_ERROR,
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_REINDEX,
    ];

    protected array $allowedStarters = [
        self::STARTER_CRON,
        self::STARTER_SAVE_ACTION,
    ];

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @OVERRIDE
     * manage created_at and updated_at fields here
     * @return AbstractModel
     */
    public function beforeSave(): AbstractModel
    {
        parent::beforeSave();

        $now = date("Y-m-d H:i:s");

        if ($this->isObjectNew() && !$this->getCreatedAt()) {
            $this->setCreatedAt($now);
        }

        $this->setUpdatedAt($now);

        return $this;
    }

    /**
     * @param string $startedBy
     * @return void
     * @throws InputException
     */
    public function setStartedBy(string $startedBy): void
    {
        if (!in_array($startedBy, $this->allowedStarters)) {
            throw new InputException(__("Invalid queue starter_by value"));
        }

        $this->setData(self::FIELD_STARTED_BY, $startedBy);
    }

    /**
     * @return string
     */
    public function getStartedBy(): string
    {
        return (string)$this->getData(self::FIELD_STARTED_BY);
    }

    /**
     * @param string $status
     * @return void
     * @throws InputException
     */
    public function setStatus(string $status): void
    {
        if (!in_array($status, $this->allowedStatuses)) {
            throw new InputException(__("Invalid queue status value"));
        }

        $this->setData(self::FIELD_STATUS, $status);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string)$this->getData(self::FIELD_STATUS);
    }

    /**
     * @param string $message
     * @return void
     */
    public function setLogMessage(string $message): void
    {
        $this->setData(self::FIELD_LOG_MESSAGE, $message);
    }

    /**
     * @return string
     */
    public function getLogMessage(): string
    {
        return (string)$this->getData(self::FIELD_LOG_MESSAGE);
    }

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::FIELD_CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::FIELD_CREATED_AT);
    }

    /**
     * @param string|null $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt = null): void
    {
        $this->setData(self::FIELD_UPDATED_AT, $updatedAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string)$this->getData(self::FIELD_UPDATED_AT);
    }

    /**
     * @param array $skus
     * @return void
     */
    public function setSkus(array $skus): void
    {
        if ($skus) {
            $this->setData(self::FIELD_SKUS, json_encode($skus));
        }
    }

    /**
     * @return string[]
     */
    public function getSkus(): array
    {
        $skus = [];
        $data = $this->getData(self::FIELD_SKUS);

        if ($data) {
            $skus = json_decode($data);
        }

        return $skus;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            self::FIELD_STARTED_BY => $this->getStartedBy(),
            self::FIELD_STATUS => $this->getStatus(),
            self::FIELD_LOG_MESSAGE => $this->getLogMessage(),
            self::FIELD_SKUS => $this->getSkus(),
            self::FIELD_CREATED_AT => $this->getCreatedAt(),
            self::FIELD_UPDATED_AT => $this->getUpdatedAt(),
        ];
    }
}
