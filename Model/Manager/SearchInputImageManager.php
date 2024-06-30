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

use BelSmol\VisualSearch\API\Data\SearchInputImageInterface;
use BelSmol\VisualSearch\API\Data\SearchInputImageInterfaceFactory;
use BelSmol\VisualSearch\API\SearchInputImageManagerInterface;
use BelSmol\VisualSearch\Exception\InvalidSearchInputException;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SearchInputImageManager
 * Process a user's search input image
 * @package BelSmol\VisualSearch\Model\Manager
 */
class SearchInputImageManager implements SearchInputImageManagerInterface
{
    const TEMP_IMG_DIR = 'tmp/visual_search';

    protected const INDEX_IMAGE_DATA_MIME = 0;
    protected const INDEX_IMAGE_DATA_CONTENT = 1;
    protected const INDEX_IMAGE_WIDTH = 0;
    protected const INDEX_IMAGE_HEIGHT = 1;
    protected const INDEX_IMAGE_MIME = 'mime';
    protected const INDEX_MIME_DATA = 0;
    protected const INDEX_MIME_DATA_EXTENSION = 1;
    protected const DATA_WITH_COLON_LENGTH = 5;
    protected const MAX_SPLIT_ELEMENT_COUNT = 2;

    /**
     * @param SearchInputImageInterfaceFactory $searchInputImageInterfaceFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param array $allowedMimeType
     */
    public function __construct(
        protected SearchInputImageInterfaceFactory $searchInputImageInterfaceFactory,
        protected Filesystem $filesystem,
        protected StoreManagerInterface $storeManager,
        protected array $allowedMimeType = ['image/jpeg', 'image/png']
    ) {}

    /**
     * Prepare input search image.
     * Convert to a file and save to temp dir in pub/media/tmp/visual_search folder
     *
     * @param string $base64ImgData
     * @return SearchInputImageInterface
     * @throws FileSystemException|InvalidSearchInputException|NoSuchEntityException
     */
    public function prepareImage(string $base64ImgData): SearchInputImageInterface
    {
        if (!$this->isValidBase64($base64ImgData)) {
            throw new InvalidSearchInputException(__("Invalid input search image string"));
        }

        $image = $this->searchInputImageInterfaceFactory->create();

        $image->setBase64Content($base64ImgData);

        $content = $this->getBase64CleanContent($base64ImgData);
        $image->setContent($content);

        $extension = $this->getImageExtension($base64ImgData);
        $image->setExtension($extension);

        $fileName = $this->generateFileName($extension);
        $image->setFileName($fileName);

        $absolutePath = $this->saveBase64ImgToTemp($content, $fileName);
        $image->setAbsolutePath($absolutePath);

        $imageUrl = $this->getImageUrl($fileName);
        $image->setImageUrl($imageUrl);

        $pathInPubMedia = $this->getPathInPubMedia($fileName);
        $image->setPathInPubMedia($pathInPubMedia);

        return $image;
    }

    /**
     * Remove image from temp directory
     * @param SearchInputImageInterface $image
     * @return void
     * @throws FileSystemException
     */
    public function removeImage(SearchInputImageInterface $image): void
    {
        $this->getMediaDirectory()->delete($image->getAbsolutePath());
    }

    /**
     * @param string $base64ImgData
     * @return bool
     */
    protected function isValidBase64(string $base64ImgData): bool
    {
        try {
            $binary = base64_decode(explode(',', $base64ImgData)[1]);
            $data = getimagesizefromstring($binary);
        } catch (Exception $e) {
            return false;
        }

        if (!$data) {
            return false;
        }

        if (
            !empty($data[self::INDEX_IMAGE_WIDTH])
            && !empty($data[self::INDEX_IMAGE_HEIGHT])
            && !empty($data[self::INDEX_IMAGE_MIME])
        ) {
            if (in_array($data[self::INDEX_IMAGE_MIME], $this->allowedMimeType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $base64ImgData
     * @return string
     */
    protected function getBase64CleanContent(string $base64ImgData): string
    {
        $cleanImageString = substr($base64ImgData, self::DATA_WITH_COLON_LENGTH); //remove "data:"
        $imageData = explode(',', $cleanImageString , self::MAX_SPLIT_ELEMENT_COUNT);

        return $imageData[self::INDEX_IMAGE_DATA_CONTENT];
    }

    /**
     * @param string $base64ImgData
     * @return string
     */
    protected function getImageExtension(string $base64ImgData): string
    {
        $cleanImageString = substr($base64ImgData, self::DATA_WITH_COLON_LENGTH); //remove "data:"

        $imageData = explode(
            ',',
            $cleanImageString ,
            self::MAX_SPLIT_ELEMENT_COUNT
        );

        $mimeSplitWithoutBase64 = explode(
            ';',
            $imageData[self::INDEX_IMAGE_DATA_MIME],
            self::MAX_SPLIT_ELEMENT_COUNT
        );

        $mimeSplit = explode(
            '/',
            $mimeSplitWithoutBase64[self::INDEX_MIME_DATA],
            self::MAX_SPLIT_ELEMENT_COUNT
        );

        return $mimeSplit[self::INDEX_MIME_DATA_EXTENSION] == 'jpeg'
            ? 'jpg'
            : $mimeSplit[self::INDEX_MIME_DATA_EXTENSION];
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function generateFileName(string $extension): string
    {
        return md5(time().uniqid()) . '.' . $extension;
    }

    /**
     * @param string $content
     * @param string $fileName
     * @return string
     * @throws FileSystemException
     */
    protected function saveBase64ImgToTemp(string $content, string $fileName): string
    {
        $absolutePath = $this->getMediaDirectory()->getAbsolutePath(self::TEMP_IMG_DIR);
        $this->getMediaDirectory()->create($absolutePath);

        $file = $absolutePath . DIRECTORY_SEPARATOR . $fileName;

        file_put_contents($file, base64_decode($content));

        return $file;
    }

    /**
     * @param string $fileName
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getImageUrl(string $fileName): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA );
        return $mediaUrl . self::TEMP_IMG_DIR . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @return WriteInterface
     * @throws WriteInterface|FileSystemException
     */
    protected function getMediaDirectory(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getPathInPubMedia(string $fileName): string
    {
        return self::TEMP_IMG_DIR . DIRECTORY_SEPARATOR . $fileName;
    }
}
