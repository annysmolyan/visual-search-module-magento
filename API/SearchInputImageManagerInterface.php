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

use BelSmol\VisualSearch\API\Data\SearchInputImageInterface;

/**
 * @api
 * Interface SearchInputImageManagerInterface
 * Process a user's search input image
 * @package BelSmol\VisualSearch\API
 */
interface SearchInputImageManagerInterface
{
    /**
     * @param string $base64ImgData
     * @return SearchInputImageInterface
     */
    public function prepareImage(string $base64ImgData): SearchInputImageInterface;

    /**
     * @param SearchInputImageInterface $image
     * @return void
     */
    public function removeImage(SearchInputImageInterface $image): void;
}
