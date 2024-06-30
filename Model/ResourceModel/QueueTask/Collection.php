<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\ResourceModel\QueueTask;

use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\Model\QueueTask as Model;
use BelSmol\VisualSearch\Model\ResourceModel\QueueTask as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package BelSmol\VisualSearch\Model\ResourceModel\QueueTask
 */
class Collection extends AbstractCollection
{
    /** @OVERRIDE  */
    protected $_idFieldName = QueueTaskInterface::FIELD_ENTITY_ID;

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
