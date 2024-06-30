<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Config\Source;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Category
 * Return category values for config
 * @package BelSmol\VisualSearch\Model\Config\Source
 */
class Category implements OptionSourceInterface
{
    //need to keep this const public
    //they are used in the template for category selector
    public const INDEX_VALUE = "value";
    public const INDEX_LABEL = "label";
    public const INDEX_PARENT_ID = "parent_id";

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(protected CollectionFactory $collectionFactory)
    {}

    /**
     * Return category values for config
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $categoryCollection = $this->collectionFactory->create()->addAttributeToSelect([CategoryInterface::KEY_NAME]);
        $optionArray = [];

        foreach ($categoryCollection as $category) {
            $optionArray[] = [
                self::INDEX_VALUE => (int)$category->getId(),
                self::INDEX_LABEL => $category->getName(),
                self::INDEX_PARENT_ID => (int)$category->getParentId()
            ];
        }

        return $optionArray;
    }
}
