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
 * Interface ElasticsearchRequestInterface
 * Request object for elasticsearch
 * @package BelSmol\VisualSearch\API\Data
 */
interface ElasticsearchRequestInterface
{
    /**
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * @return array
     */
    public function getBody(): array;

    /**
     * @return string
     */
    public function toJson(): string;
}
