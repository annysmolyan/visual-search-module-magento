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
use BelSmol\VisualSearch\API\ElasticsearchKnnRequestInterface;
use BelSmol\VisualSearch\API\SearchManagerInterface;
use BelSmol\VisualSearch\API\VectorGeneratorInterface;
use BelSmol\VisualSearch\Exception\InvalidSearchInputException;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Profiler;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SearchManager
 * Main logic for search
 * @package BelSmol\VisualSearch\Model\Manager
 */
class SearchManager implements SearchManagerInterface
{
    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigStorageInterface $configStorage
     * @param VectorGeneratorInterface $vectorGenerator
     * @param ElasticsearchKnnRequestInterface $knnSearch
     * @param Config $catalogConfig
     */
    public function __construct(
        protected CategoryCollectionFactory $categoryCollectionFactory,
        protected ProductCollectionFactory $productCollectionFactory,
        protected StoreManagerInterface $storeManager,
        protected ConfigStorageInterface $configStorage,
        protected VectorGeneratorInterface $vectorGenerator,
        protected ElasticsearchKnnRequestInterface $knnSearch,
        protected Config $catalogConfig
    ) {}

    /**
     * @param string $imagePubMediaPath
     * @param array $categoryIds
     * @return ProductCollection
     * @throws InvalidSearchInputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSimilarProductsByImage(
        string $imagePubMediaPath,
        array  $categoryIds = []
    ): ProductCollection
    {
        if (!$this->allowUserSelectCategories() && $categoryIds) {
            throw new InvalidSearchInputException(__("Category selection is not allowed"));
        }

        $inputImageVector = $this->vectorGenerator->generateSearchImageVector($imagePubMediaPath);
        $vsDataCollection = $this->knnSearch->search($inputImageVector);

        $productIds = [];

        foreach ($vsDataCollection->getItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        Profiler::start('BelSmol_VisualSearch:' . __METHOD__);

        $productCollection = $this->getSearchProductCollection($categoryIds, null, $productIds);

        Profiler::stop('BelSmol_VisualSearch:' . __METHOD__);

        return $productCollection;
    }

    /**
     * @return bool
     */
    public function allowUserSelectCategories(): bool
    {
        return $this->configStorage->isEnabledCategorySelection();
    }

    /**
     * @param array $selectedByUserCategoryIds
     * @param int|null $storeId
     * @return CategoryCollection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchCategoryCollection(
        array $selectedByUserCategoryIds = [],
        int $storeId = null
    ): CategoryCollection
    {
        $categoryCollection = $this->categoryCollectionFactory->create();

        $categoryCollection->addAttributeToSelect(CategoryInterface::KEY_NAME);

        $collectionStore = (null == $storeId) ? $this->storeManager->getStore() : $storeId;
        $categoryCollection->setStore($collectionStore);

        if ($this->configStorage->allCategoriesIncluded()) {
            $excludedIds = $this->configStorage->getExcludedCategoriesIds();
            if ($excludedIds) {
                $categoryCollection->addFieldToFilter('entity_id', ['nin' => $excludedIds]);
            }
            if ($this->allowUserSelectCategories() && $selectedByUserCategoryIds) {
                $allowedCategories = array_diff($selectedByUserCategoryIds, $excludedIds);
                $categoryCollection->addFieldToFilter('entity_id', ['in' => $allowedCategories ?: []]);
            }
        } else {
            $includedIds = $this->configStorage->getIncludedCategoriesIds();

            if ($this->allowUserSelectCategories() && $selectedByUserCategoryIds) {
                $allowedCategories = array_intersect($selectedByUserCategoryIds, $includedIds);
                $categoryCollection->addFieldToFilter('entity_id', ['in' => $allowedCategories ?: []]);
            } else {
                $categoryCollection->addFieldToFilter('entity_id', ['in' => $includedIds]);
            }
        }

        return $categoryCollection;
    }

    /**
     * Only visible in catalog, search or search and enabled products will be returned
     * @param array $selectedByUserCategoryIds
     * @param int|null $storeId
     * @param array $productIds
     * @return ProductCollection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchProductCollection(
        array $selectedByUserCategoryIds = [],
        int $storeId = null,
        array $productIds = []
    ): ProductCollection
    {
        $collectionStore = (null == $storeId) ? (int)$this->storeManager->getStore()->getId() : $storeId;
        $searchCategoryIds = $this->getSearchCategoryCollection($selectedByUserCategoryIds, $collectionStore)
            ->getColumnValues('entity_id');

        $collection = $this->productCollectionFactory
            ->create()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setStoreId($collectionStore)
            ->addStoreFilter($collectionStore)
            ->addCategoriesFilter(['in' => $searchCategoryIds])
            ->addAttributeToFilter(ProductInterface::STATUS, Status::STATUS_ENABLED)
            ->addAttributeToFilter(
                ProductInterface::VISIBILITY,
                ['in' => Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_SEARCH]
            );

        $collection->addAttributeToFilter('entity_id', ['in' => $productIds ?? 'NULL']);

        return $collection;
    }
}
