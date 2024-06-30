<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Elasticsearch;

use BelSmol\VisualSearch\API\Data\ElasticsearchRequestInterface;

/**
 * Class DTORequest
 * Request object for elasticsearch
 * @package BelSmol\VisualSearch\Model\Elasticsearch
 */
class DTORequest implements ElasticsearchRequestInterface
{
    /**
     * @param string $endpoint
     * @param array $body
     */
    public function __construct(
        protected string $endpoint,
        protected array $body
    ) {}

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
       return $this->body;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->getBody());
    }
}
