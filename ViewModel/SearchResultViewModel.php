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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Output;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SearchResultViewModel
 * @package BelSmol\VisualSearch\ViewModel
 */
class SearchResultViewModel implements ArgumentInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param Output $outputHelper
     */
    public function __construct(
        protected StoreManagerInterface $storeManager,
        protected Output $outputHelper
    ) {}

    /**
     * @param string $imagePath
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSearchImageUrl(string $imagePath): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $imagePath;
    }

    /**
     * @param ProductInterface $product
     * @param string $attributeHtml
     * @param string $attributeName
     * @return string
     * @throws LocalizedException
     */
    public function getProductAttributeOutput(
        ProductInterface $product,
        string $attributeHtml,
        string $attributeName
    ): string
    {
        return $this->outputHelper->productAttribute($product, $attributeHtml, $attributeName);
    }
}
