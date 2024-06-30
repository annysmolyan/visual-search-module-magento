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

use BelSmol\VisualSearch\API\Data\HttpRemoteDTOResponseInterface;

/**
 * Class DTOResponse
 * Used for external API responses
 * @package BelSmol\VisualSearch\HttpRemote
 */
class DTOResponse implements HttpRemoteDTOResponseInterface
{
    /**
     * @param int $status
     * @param array $data
     */
    public function __construct(
        protected int $status,
        protected array $data = []
    ) {}

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
