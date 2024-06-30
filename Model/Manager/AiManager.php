<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Manager;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\AiManagerInterface;
use Exception;

/**
 * Class AiManager
 * Tensorflow application config manager
 * @package BelSmol\VisualSearch\Model\Manager
 */
class AiManager implements AiManagerInterface
{
    protected const CNN_MODEL_INCEPTION_V3 = 'InceptionV3';
    protected const VECTOR_DIMENSION_INCEPTION_V3 = 1000;

    protected const ENDPOINT_BY_IMG_PATH = 'feature-extract/path-source';
    protected const ENDPOINT_BY_CSV = 'feature-extract/csv-source';

    /**
     * @param ConfigStorageInterface $configStorage
     */
    public function __construct(protected ConfigStorageInterface $configStorage)
    {}

    /**
     * @return string
     */
    public function getSingleImageExtractorEndpoint(): string
    {
        return $this->configStorage->getAiServerDomain() . self::ENDPOINT_BY_IMG_PATH;
    }

    /**
     * @return string
     */
    public function getCsvExtractorEndpoint(): string
    {
        return $this->configStorage->getAiServerDomain() . self::ENDPOINT_BY_CSV;
    }

    /**
     * @return string
     */
    public function getCurrentCnnModel(): string
    {
        return $this->configStorage->getCnnModel();
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCurrentCnnModelVectorDimension(): int
    {
        $currentCnnModel = $this->getCurrentCnnModel();
        return $this->getCnnModelVectorDimension($currentCnnModel);
    }

    /**
     * @return string[]
     */
    public function getCnnModelsList(): array
    {
        return [
            self::CNN_MODEL_INCEPTION_V3,
        ];
    }

    /**
     * This dimension is used for creating vector in elastic search.
     * WARNING! Dimension must be the same for: CNN model, elastic search, search vector.
     * Otherwise, fatal error will be occurred
     *
     * @param string $modelName
     * @return int
     * @throws Exception
     */
    public function getCnnModelVectorDimension(string $modelName): int
    {
        return match ($modelName) {
            self::CNN_MODEL_INCEPTION_V3 => self::VECTOR_DIMENSION_INCEPTION_V3,
            default => throw new Exception("Can't return vector dimension, unknown Cnn model name"),
        };
    }
}
