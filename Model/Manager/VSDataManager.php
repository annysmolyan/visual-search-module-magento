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

use BelSmol\VisualSearch\API\CliLoggerInterface;
use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\DBManagerInterface;
use BelSmol\VisualSearch\API\VSDataManagerInterface;
use BelSmol\VisualSearch\API\VectorGeneratorInterface;
use BelSmol\VisualSearch\Exception\DefaultStoreNotAllowedException;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class VSDataManager
 * Manage visual search data table data
 * @package BelSmol\VisualSearch\Model\Manager
 */
class VSDataManager implements VSDataManagerInterface
{
    protected const CATALOG_PRODUCT_MEDIA_FOLDER = "catalog/product";
    protected const DEFAULT_PRODUCT_BATCH_SIZE = 100;
    protected const NO_SELECTION_VALUE = 'no_selection';

    private const VECTOR_INDEX = 2;
    private const SMALL_IMAGE_INDEX = 3;
    private const PRODUCT_ID_INDEX = 0;

    protected ReadInterface $pubMediaDirectory;
    protected array $vectorData = [];
    protected array $allowedVisibility = [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_SEARCH];
    protected int $batchSize = self::DEFAULT_PRODUCT_BATCH_SIZE;

    /**
     * @param Filesystem $fileSystem
     * @param ConfigStorageInterface $configStorage
     * @param CliLoggerInterface $cliLogger
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $productCollectionFactory
     * @param VectorGeneratorInterface $imageVectorGenerator
     * @param DBManagerInterface $dbManager
     */
    public function __construct(
        Filesystem $fileSystem,
        protected ConfigStorageInterface $configStorage,
        protected CliLoggerInterface $cliLogger,
        protected StoreManagerInterface $storeManager,
        protected CollectionFactory $productCollectionFactory,
        protected VectorGeneratorInterface $imageVectorGenerator,
        protected DBManagerInterface $dbManager
    ) {
        $this->pubMediaDirectory = $fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->batchSize = $this->configStorage->getSearchDataBatchSize();
    }

    /**
     * This method will truncate all data and generate it from scratch
     * Warning! Direct sql queries is used here, it's faster that repository.
     * Mind that temp table is used here
     *
     * IS HIGH LOADING! USE ONLY VIA CLI!
     *
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function createFullCatalogVisualSearchData(): void
    {
        if (!$this->configStorage->isElasticVersionCorrect()) {
            throw new Exception('Error: Make sure you have Elasticsearch >= 8.0');
        }

        // 1. Create temp table
        $this->cliLogger->printWarning("Creating tmp table:");
        $this->dbManager->createVSDataTmpTable();
        $this->dbManager->truncateVSDataTmpTable();
        $this->cliLogger->printSuccess("Done");

        // 2. get store list array
        $storeList = $this->storeManager->getStores();
        $this->cliLogger->printMessage("Total Store Count: " . count($storeList));

        // 3. save data per store into temp table
        foreach ($storeList as $store) {
            $storeId = (int)$store->getId();
            $this->cliLogger->printMessage("Generate Data For Store ID: " . $storeId);
            $this->saveDataForStoreInTmpTable($storeId);
            $this->cliLogger->printMessage(""); // need for a new line
        }

        // 4. truncate original table and insert data from temp table
        $this->cliLogger->printWarning("Prepare main table:");
        $this->dbManager->truncateVSDataTable();
        $this->dbManager->copyContentFromVSDataTmpTable();
        $this->cliLogger->printSuccess("Done");

        // 5. remove temp table
        $this->cliLogger->printWarning("Remove tmp table:");
        $this->dbManager->deleteVSDataTmpTable();
        $this->cliLogger->printSuccess("Done");

        $this->cliLogger->printSuccess("Data generation done");
    }

    /**
     * If not empty skus then only specific products will be checked for outdated data,
     * otherwise all products will be considered
     * Array of processed entity_ids of visual_search data will be returned
     * for further reindex process
     *
     * @param array $skus
     * @return array
     * @throws Exception
     */
    public function updateVisualSearchData(array $skus = []): array
    {
        if (!$this->configStorage->isElasticVersionCorrect()) {
            throw new Exception('Error: Make sure you have Elasticsearch >= 8.0');
        }

        $storeList = $this->storeManager->getStores();
        $this->cliLogger->printMessage("Total Store Count: " . count($storeList));
        $updatedRowIds = [];

        foreach ($storeList as $store) {
            $storeId = (int)$store->getId();

            $this->cliLogger->printMessage("Generate Data For Store ID: " . $storeId);

            $oldRows = [];

            // remove from index unwilling products
            if ($productIdsToRemove = $this->getProductIdsToBeRemovedFromIndex($storeId)) {
                $oldRows = $this->dbManager->getVsDataEntityIdListByProductIds($storeId, $productIdsToRemove);
                $this->dbManager->removeFromVsDataTable($storeId, $productIdsToRemove);
                $updatedRowIds = array_unique(array_merge($updatedRowIds, $oldRows));
            }

            //process outdated products
            $outdatedProductCollection = $this->getOutdatedProductCollection($storeId, $skus);
            $products = $outdatedProductCollection->getItems();

            if (!$products) {
                $this->cliLogger->printWarning("No Data For Store ID: " . $storeId);
                continue;
            }

            $productCount = count($products);
            $productIds = $outdatedProductCollection->getAllIds();
            $oldRows = array_unique(
                array_merge($oldRows, $this->dbManager->getVsDataEntityIdListByProductIds($storeId, $productIds))
            );

            $this->cliLogger->printMessage("Found products: " . $productCount);

            $this->dbManager->removeFromVsDataTable($storeId, $productIds);

            $iterationBatches = array_chunk($products, $this->batchSize);
            $processedProducts = 0;

            foreach ($iterationBatches as $batchData) {
                $batchQueryData = $this->getBatchQueryData($batchData, $storeId);

                if ($batchQueryData) {
                    $this->dbManager->insertIntoVSDataTable($batchQueryData);
                    $processedProducts += count($batchQueryData);
                    $this->cliLogger->printProgressBar($processedProducts, $productCount);
                }
            }

            $newRows = $this->dbManager->getVsDataEntityIdListByProductIds($storeId, $productIds);
            $updatedRowIds = array_unique(array_merge($updatedRowIds, $oldRows, $newRows));

            $this->cliLogger->printMessage(""); // need for a new line
        }

        return $updatedRowIds;
    }

    /**
     * Product which cant be in visual_search data anymore,
     * like disabled products
     *
     * @param int $storeId
     * @return array
     */
    public function getProductIdsToBeRemovedFromIndex(int $storeId): array
    {
        $productCollection = $this->productCollectionFactory->create();

        $productCollection
            ->addAttributeToSelect(ProductInterface::STATUS)
            ->addStoreFilter($storeId)
            ->addAttributeToFilter(ProductInterface::STATUS, Status::STATUS_DISABLED);

        $productCollection->getSelect()->join(
            ['vsd' => $this->dbManager->getTableName("visual_search_data")],
            sprintf("
                (vsd.product_id = e.entity_id)
                AND vsd.store_id = %s
            ", $storeId));

        return $productCollection->getAllIds();
    }

    /**
     * Get products which can be processed for visual search
     * @param int $storeId
     * @param array $skus
     * @return ProductCollection
     */
    public function getSearchableProductCollection(int $storeId, array $skus = []): ProductCollection
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addAttributeToSelect([Images::CODE_SMALL_IMAGE, ProductInterface::STATUS, ProductInterface::VISIBILITY])
            ->addStoreFilter($storeId)
            ->addAttributeToFilter(ProductInterface::STATUS, Status::STATUS_ENABLED)
            ->addAttributeToFilter(ProductInterface::VISIBILITY, ['in' => $this->allowedVisibility]);

        if ($skus) {
            $productCollection->addFieldToFilter('sku', ['in' => $skus]);
        }

        return $productCollection;
    }

    /**
     * Get products which images differ from current visual_search index data
     * @param int $storeId
     * @param array $skus
     * @return ProductCollection
     */
    public function getOutdatedProductCollection(int $storeId, array $skus = []): ProductCollection
    {
        $attributeId = $this->dbManager->getEavAttributeId(Images::CODE_SMALL_IMAGE);
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addAttributeToSelect([ProductInterface::STATUS, ProductInterface::VISIBILITY])
            ->addAttributeToFilter(ProductInterface::STATUS, Status::STATUS_ENABLED)
            ->addAttributeToFilter(ProductInterface::VISIBILITY, ['in' => $this->allowedVisibility]);

        $productCollection->getSelect()->join(
            ['small_image_default' => $this->dbManager->getTableName("catalog_product_entity_varchar")],
            sprintf("
                (small_image_default.entity_id = e.entity_id)
                AND (small_image_default.attribute_id = '%s')
                AND small_image_default.store_id = %s
            ", $attributeId, Store::DEFAULT_STORE_ID),
            ["IF(small_image.value_id is not null, small_image.value, small_image_default.value) AS small_image"]
        )->joinLeft(
            ['small_image' => $this->dbManager->getTableName("catalog_product_entity_varchar")],
            sprintf("
                (small_image.entity_id = e.entity_id)
                AND (small_image.attribute_id = '%s')
                AND small_image.store_id = %s
            ", $attributeId, $storeId),
            []
        )->joinLeft(
            ['vsd' => $this->dbManager->getTableName("visual_search_data")],
            sprintf("
                (vsd.product_id = e.entity_id)
                AND vsd.store_id = %s
            ", $storeId),
            []
        )->where("(vsd.product_id IS NULL OR vsd.path != IF(small_image.value_id is not null, small_image.value, small_image_default.value))");

        if ($skus) {
            $productCollection->addFieldToFilter('sku', ['in' => $skus]);
        }

        return $productCollection;
    }

    /**
     * Generate product visual search data for each store and save in temp table.
     * WARNING! Only SMALL_IMAGE will be considered to prevent high performance loading
     *
     * @param int $storeId
     * @throws DefaultStoreNotAllowedException
     */
    protected function saveDataForStoreInTmpTable(int $storeId): void
    {
        if (!$this->isStoreIdAllowed($storeId)) {
            throw new DefaultStoreNotAllowedException(__("Default store is not allowed"));
        }

        $productCollection = $this->getSearchableProductCollection($storeId);

        $products = $productCollection->getItems();
        $productCount = count($products);

        $this->cliLogger->printMessage("Found products: " . $productCount);

        $processedProducts = 0;
        $iterationBatches = array_chunk($products, $this->batchSize);

        foreach ($iterationBatches as $batchData) {
            $batchQueryData = $this->getBatchQueryData($batchData, $storeId);

            if ($batchQueryData) {
                $processedProducts += count($batchQueryData);
                $this->dbManager->insertIntoVSDataTmpTable($batchQueryData);
                $this->cliLogger->printProgressBar($processedProducts, $productCount);
            }
        }
    }

    /**
     * Process batch and return query data array
     * @param array $batchData
     * @param int $storeId
     * @return array
     */
    protected function getBatchQueryData(array $batchData, int $storeId): array
    {
        $iterationQueryData = [];
        $iterationProductImageUrls = [];

        foreach ($batchData as $product) {
            $smallImage = $product->getSmallImage();

            if (!$this->isValidSmallImage((string)$smallImage)) {
                $this->cliLogger->printWarning(
                    sprintf("Skipped product with sku '%s'. Invalid image", $product->getSku())
                );
                continue;
            }

            $iterationProductImageUrls[] = $smallImage;
            $iterationQueryData[] = [
                $product->getId(),
                $storeId,
                null, // in this step marks as null
                $smallImage
            ];
        }

        return $this->processIterationQueryData($iterationQueryData, $iterationProductImageUrls);
    }

    /**
     * Get image vectors and prepare final query data
     * @param array $iterationQueryData
     * @param array $iterationProductImageUrls
     * @return array
     */
    protected function processIterationQueryData(array &$iterationQueryData, array $iterationProductImageUrls): array
    {
        $uniqueImages = array_unique(array_values($iterationProductImageUrls));
        $absentVectorData = array_diff($uniqueImages, array_keys($this->vectorData));

        if ($absentVectorData) {
            $vectors = $this->imageVectorGenerator->generateProductsVectors($iterationProductImageUrls);
            $this->vectorData = array_merge($this->vectorData, $vectors);
        }

        foreach ($iterationQueryData as &$query) {
            $vector = $this->vectorData[$query[self::SMALL_IMAGE_INDEX]] ?? ''; // vector will be returned as json

            if ($vector) {
                $query[self::VECTOR_INDEX] = $vector; //save json in DB
            } else {
                $this->cliLogger->printWarning(
                    sprintf("Skipped product with id '%s'. Empty vector", $query[self::PRODUCT_ID_INDEX])
                );
                unset($query);
            }
        }

        return $iterationQueryData;
    }

    /**
     * @param string $smallImage
     * @return bool
     */
    protected function isValidSmallImage(string $smallImage): bool
    {
        return $smallImage && $smallImage != self::NO_SELECTION_VALUE && $this->isImageFileExists($smallImage);
    }

    /**
     * @param string $smallImage
     * @return bool
     */
    protected function isImageFileExists(string $smallImage): bool
    {
        $pathInPubMedia = self::CATALOG_PRODUCT_MEDIA_FOLDER . $smallImage;
        $absolutePath = $this->pubMediaDirectory->getAbsolutePath($pathInPubMedia);

        return file_exists($absolutePath);
    }

    /**
     * Don't use admin store for data generation
     * @param int $storeId
     * @return bool
     */
    protected function isStoreIdAllowed(int $storeId): bool
    {
        return $storeId != Store::DEFAULT_STORE_ID;
    }
}
