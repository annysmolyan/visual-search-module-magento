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
 * Interface QueueTaskSearchResultInterface
 * Is used as a search result in repository
 * WARNING: Don't use here return type because of model Magento\Framework\Api\SearchResults inheriting
 * @package BelSmol\VisualSearch\API\Data
 */
interface QueueTaskSearchResultInterface
{
    /**
     * @return QueueTaskInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return QueueTaskInterface
     */
    public function setItems(array $items);
}
