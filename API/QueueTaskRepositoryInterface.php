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

use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\API\Data\QueueTaskSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 * Interface QueueTaskRepositoryInterface
 * @package BelSmol\VisualSearch\API
 */
interface QueueTaskRepositoryInterface
{
    /**
     * @param int $id
     * @return QueueTaskInterface
     */
    public function getById(int $id): QueueTaskInterface;

    /**
     * @param QueueTaskInterface $task
     */
    public function save(QueueTaskInterface $task): QueueTaskInterface;

    /**
     * @param QueueTaskInterface $task
     * @return void
     */
    public function delete(QueueTaskInterface $task): void;

    /**
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return QueueTaskSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): QueueTaskSearchResultInterface;
}
