<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Block;

use BelSmol\VisualSearch\API\Data\SearchRequestInterface;
use BelSmol\VisualSearch\API\SearchManagerInterface;
use BelSmol\VisualSearch\API\SearchRequestManagerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Image as ProductImageBlock;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Profiler;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class VisualSearchResult
 * This block is used for displaying visual search results
 * @package BelSmol\VisualSearch\Block
 */
class VisualSearchResult extends ListProduct
{
    protected const SEARCH_PARAM_NAME = 'search';

    protected ?SearchRequestInterface $searchRequest = null;

    /**
     * @param ImageFactory $imageFactory
     * @param RequestInterface $request
     * @param SearchManagerInterface $searchManager
     * @param SearchRequestManagerInterface $searchRequestManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param array $data
     * @param Output|null $outputHelper
     */
    public function __construct(
        protected ImageFactory $imageFactory,
        protected RequestInterface $request,
        protected SearchManagerInterface $searchManager,
        protected SearchRequestManagerInterface $searchRequestManager,
        protected ProductCollectionFactory $productCollectionFactory,
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = [],
        ?Output $outputHelper = null)
    {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data,
            $outputHelper
        );
    }

    /**
     * Return data for visual search request
     * @return SearchRequestInterface|null
     */
    public function getSearchRequest(): ?SearchRequestInterface
    {
        if (null == $this->searchRequest) {
            Profiler::start('BelSmol_VisualSearch:' . __METHOD__);
            $searchParam = $this->request->getParam(self::SEARCH_PARAM_NAME, "");
            $this->searchRequest = $this->searchRequestManager->getBySearchTermValue($searchParam);
            Profiler::stop('BelSmol_VisualSearch:' . __METHOD__);
        }

        return $this->searchRequest;
    }

    /**
     * @param ProductInterface $product
     * @param string $imageDisplayArea
     * @param array $attributes
     * @return ProductImageBlock
     */
    public function createProductImageBlock(
        ProductInterface $product,
        string $imageDisplayArea,
        array $attributes = []
    ): ProductImageBlock
    {
        return $this->imageFactory->create($product, $imageDisplayArea, $attributes);
    }

    /**
     * Copied because of private method execution
     * @return string
     */
    public function getMode(): string
    {
        if ($this->getChildBlock('toolbar')) {
            return $this->getChildBlock('toolbar')->getCurrentMode();
        }

        return $this->getDefaultListingMode();
    }

    /**
     * @OVERRIDE
     * @return VisualSearchResult
     * @throws LocalizedException
     */
    protected function _beforeToHtml(): BlockInterface
    {
        $collection = $this->_getProductCollection();

        $this->addToolbarBlock($collection);

        if (!$collection->isLoaded()) {
            $collection->load();
        }

        return parent::_beforeToHtml();
    }

    /**
     * Copied because of private method execution
     * @return ProductCollection
     */
    protected function _getProductCollection(): ProductCollection
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * @return ProductCollection
     */
    protected function initEmptyCollection(): ProductCollection
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter(ProductInterface::SKU, ['in' => 'NULL']);

        return $collection;
    }

    /**
     * Either load data from visual search request
     * or just return empty collection
     * @return ProductCollection
     */
    private function initializeProductCollection(): ProductCollection
    {
        if (null === $this->_productCollection) {
            $searchRequest = $this->getSearchRequest();
            $this->_productCollection = (null === $searchRequest)
                ? $this->initEmptyCollection()
                : $this->searchManager->getSimilarProductsByImage(
                    trim($searchRequest->getImagePath()),
                    $searchRequest->getCategories()
                );
        }

        return $this->_productCollection;
    }

    /**
     * Copied from parent class, because it's private
     * @return string
     */
    private function getDefaultListingMode(): string
    {
        // default Toolbar when the toolbar layout is not used
        $defaultToolbar = $this->getToolbarBlock();
        $availableModes = $defaultToolbar->getModes();

        // layout config mode
        $mode = $this->getData('mode');

        if (!$mode || !isset($availableModes[$mode])) {
            // default config mode
            $mode = $defaultToolbar->getCurrentMode();
        }

        return (string)$mode;
    }

    /**
     * Copied from parent class, because it's private
     * @param ProductCollection $collection
     * @return void
     * @throws LocalizedException
     */
    private function addToolbarBlock(ProductCollection $collection): void
    {
        $toolbarLayout = $this->getToolbarFromLayout();

        if ($toolbarLayout) {
            $this->configureToolbar($toolbarLayout, $collection);
        }
    }

    /**
     * Copied from parent class, because it's private
     * @return BlockInterface
     * @throws LocalizedException
     */
    private function getToolbarFromLayout(): BlockInterface
    {
        $blockName = $this->getToolbarBlockName();

        $toolbarLayout = false;

        if ($blockName) {
            $toolbarLayout = $this->getLayout()->getBlock($blockName);
        }

        return $toolbarLayout;
    }

    /**
     * Copied from parent class, because it's private
     * @param Toolbar $toolbar
     * @param ProductCollection $collection
     * @return void
     */
    private function configureToolbar(Toolbar $toolbar, ProductCollection $collection): void
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }
}
