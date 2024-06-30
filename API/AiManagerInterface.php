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

/**
 * @api
 * Interface AiManagerInterface
 * AI application config manager
 * @package BelSmol\VisualSearch\API
 */
interface AiManagerInterface
{
    /**
     * @return string
     */
    public function getSingleImageExtractorEndpoint(): string;

    /**
     * @return string
     */
    public function getCsvExtractorEndpoint(): string;

    /**
     * @return string
     */
    public function getCurrentCnnModel(): string;

    /**
     * @return int
     */
    public function getCurrentCnnModelVectorDimension(): int;

    /**
     * @return array
     */
    public function getCnnModelsList(): array;

    /**
     * @param string $modelName
     * @return int
     */
    public function getCnnModelVectorDimension(string $modelName): int;
}
