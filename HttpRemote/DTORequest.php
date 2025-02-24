<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\HttpRemote;

use BelSmol\VisualSearch\API\Data\HttpRemoteDTORequestInterface;

/**
 * Class DTORequest
 * Used for external API call requests
 * @package BelSmol\VisualSearch\HttpRemote
 */
class DTORequest implements HttpRemoteDTORequestInterface
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
}
