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
 * Interface QueueTaskInterface
 * Entity to keep queue data status in admin panel
 * @package BelSmol\VisualSearch\API\Data
 */
interface QueueTaskInterface
{
    const TABLE_NAME = "visual_search_queue";

    const FIELD_ENTITY_ID = "entity_id";
    const FIELD_STARTED_BY = "started_by";
    const FIELD_STATUS = "status";
    const FIELD_LOG_MESSAGE = "log_message";
    const FIELD_SKUS = "skus";
    const FIELD_CREATED_AT = "created_at";
    const FIELD_UPDATED_AT = "updated_at";

    const STARTER_CRON = "cron";
    const STARTER_SAVE_ACTION = "save_action";

    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";
    const STATUS_PENDING = "pending";
    const STATUS_IN_PROGRESS = "in_progress";
    const STATUS_REINDEX = "reindex run";

    /**
     * Don't use return type here
     * @return int
     */
    public function getId();

    /**
     * Don't use return type here
     * Don't use type hint here
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * @param string $startedBy
     * @return void
     */
    public function setStartedBy(string $startedBy): void;

    /**
     * @return string
     */
    public function getStartedBy(): string;

    /**
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $message
     * @return void
     */
    public function setLogMessage(string $message): void;

    /**
     * @return string
     */
    public function getLogMessage(): string;

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt): void;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string|null $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt = null): void;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param array $skus
     * @return void
     */
    public function setSkus(array $skus): void;

    /**
     * @return string[]
     */
    public function getSkus(): array;
}
