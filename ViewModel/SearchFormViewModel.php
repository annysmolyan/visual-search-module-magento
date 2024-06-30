<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\ViewModel;

use BelSmol\VisualSearch\API\SearchManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class SearchFormViewModel
 * @package BelSmol\VisualSearch\ViewModel
 */
class SearchFormViewModel implements ArgumentInterface
{
    /**
     * @param SearchManagerInterface $searchManager
     */
    public function __construct(protected SearchManagerInterface $searchManager)
    {}

    /**
     * @return bool
     */
    public function canShowCategorySelector(): bool
    {
        return $this->searchManager->allowUserSelectCategories();
    }
}
