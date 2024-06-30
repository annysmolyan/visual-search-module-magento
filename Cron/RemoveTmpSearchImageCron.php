<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Cron;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\FileCleanerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveTmpSearchImageCron
 * @package BelSmol\VisualSearch\Cron
 */
class RemoveTmpSearchImageCron
{
    /**
     * @param ConfigStorageInterface $configStorage
     * @param LoggerInterface $logger
     * @param FileCleanerInterface $fileCleaner
     */
    public function __construct(
        private ConfigStorageInterface $configStorage,
        private LoggerInterface $logger,
        private FileCleanerInterface $fileCleaner
    ) {}

    /**
     * Remove temporary search images
     * @return void
     */
    public function execute(): void
    {
        if (!$this->canRunCron()) {
            $this->logger->debug(
                'Cron Job belsmol_tmp_search_clean_cron: Module disabled or can not clean by cron.'
            );
            return;
        }

        $this->fileCleaner->cleanTempSearchImagesFolder();

        $this->logger->debug(
            'Cron Job belsmol_tmp_search_clean_cron: tmp directory has been cleaned.'
        );
    }

    /**
     * @return bool
     */
    private function canRunCron(): bool
    {
        return $this->configStorage->isModuleEnabled()
            && $this->configStorage->canRemoveTmpSearchImagesByCron();
    }
}
