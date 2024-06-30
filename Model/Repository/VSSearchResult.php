<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Repository;

use BelSmol\VisualSearch\API\Data\VSSearchResultInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Class VSSearchResult
 * Class is used as a search result in repository
 * @package BelSmol\VisualSearch\Model\Repository
 */
class VSSearchResult extends SearchResults implements VSSearchResultInterface
{

}
