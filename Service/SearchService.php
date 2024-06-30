<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Service;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\SearchInputImageManagerInterface;
use BelSmol\VisualSearch\API\SearchManagerInterface;
use BelSmol\VisualSearch\API\SearchServiceInterface;
use BelSmol\VisualSearch\Exception\ModuleDisabledException;
use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Psr\Log\LoggerInterface;

/**
 * Class SearchService
 * Is used for external usage
 * e.g. in API controllers or 3-d party modules
 * @package BelSmol\VisualSearch\Service
 */
class SearchService implements SearchServiceInterface
{
    /**
     * @param LoggerInterface $logger
     * @param ConfigStorageInterface $configStorage
     * @param SearchManagerInterface $searchManager
     * @param SearchInputImageManagerInterface $searchInputImageManager
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected ConfigStorageInterface $configStorage,
        protected SearchManagerInterface $searchManager,
        protected SearchInputImageManagerInterface $searchInputImageManager
    ) {}

    /**
     * Entry point for image similarity search API
     * and external usage
     * @param string $base64ImgData
     * @param array $categoryIds
     * @return ProductCollection
     * @throws ModuleDisabledException
     */
    public function search(string $base64ImgData, array $categoryIds = []): ProductCollection
    {
        if (!$this->configStorage->isModuleEnabled()) {
            throw new ModuleDisabledException(__("Module disabled"));
        }

        try {
            $image = $this->searchInputImageManager->prepareImage($base64ImgData);
            $searchResult = $this->searchManager->getSimilarProductsByImage($image->getPathInPubMedia(), $categoryIds);
            $this->searchInputImageManager->removeImage($image);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw $exception;
        }

        return $searchResult;
    }
}
