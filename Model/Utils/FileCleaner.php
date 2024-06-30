<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Utils;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\FileCleanerInterface;
use BelSmol\VisualSearch\Model\Manager\SearchInputImageManager;
use BelSmol\VisualSearch\Model\VectorGenerator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class FileCleaner
 * Clean folders
 * @package BelSmol\VisualSearch\Model\Utils
 */
class FileCleaner implements FileCleanerInterface
{
    /**
     * @param ConfigStorageInterface $configStorage
     * @param Filesystem $filesystem
     */
    public function __construct(
        protected ConfigStorageInterface $configStorage,
        protected Filesystem $filesystem
    ) {}

    /**
     * @return void
     * @throws FileSystemException
     */
    public function cleanTempSearchImagesFolder(): void
    {
        $keepCount = $this->configStorage->getSavedTempImagesCount();
        $mediaDirectory = $this->getMediaDirectory();
        $imageList = $mediaDirectory->read(SearchInputImageManager::TEMP_IMG_DIR);

        $this->cleanFolder($mediaDirectory, $imageList, $keepCount);
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    public function cleanVectorCsvFolder(): void
    {
        $keepCount = $this->configStorage->getVectorCsvFilesSavedCount();
        $varDirectory = $this->getVarDirectory();
        $imageList = $varDirectory->read(VectorGenerator::VECTOR_CSV_FOLDER);

        $this->cleanFolder($varDirectory, $imageList, $keepCount);
    }

    /**
     * @param WriteInterface $directory
     * @param array $fileList
     * @param int $filesToKeep
     * @return void
     * @throws FileSystemException
     */
    protected function cleanFolder(WriteInterface $directory, array $fileList = [], int $filesToKeep = 0): void
    {
        if ($filesToKeep) {
            $keepFiles = array_slice($fileList, -$filesToKeep); // for save
            $removeFiles = array_diff($fileList, $keepFiles);
        } else {
            $removeFiles = $fileList;
        }

        foreach ($removeFiles as $file) {
            $directory->delete($file);
        }
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
     * @return WriteInterface
     * @throws FileSystemException
     */
    protected function getVarDirectory(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }
}
