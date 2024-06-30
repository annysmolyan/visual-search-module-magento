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
use BelSmol\VisualSearch\API\DBManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CleanQueueTableCron
 * @package BelSmol\VisualSearch\Cron
 */
class CleanQueueTableCron
{
    /**
     * @param ConfigStorageInterface $configStorage
     * @param LoggerInterface $logger
     * @param DBManagerInterface $dbManager
     */
    public function __construct(
        private ConfigStorageInterface $configStorage,
        private LoggerInterface $logger,
        private DBManagerInterface $dbManager
    ) {}

    /**
     * Clean queue table rows
     * @return void
     */
    public function execute(): void
    {
        if (!$this->canRunCron()) {
            $this->logger->debug(
                'Cron Job belsmol_queue_table_clean_cron: Module disabled or can not clean queue table by cron.'
            );
            return;
        }

        $rowsToKeep = $this->configStorage->getQueueRowsSavedCount();
        $this->dbManager->cleanQueueTable($rowsToKeep);

        $this->logger->debug(
            'Cron Job belsmol_queue_table_clean_cron: queue table has been cleaned.'
        );
    }

    /**
     * @return bool
     */
    private function canRunCron(): bool
    {
        return $this->configStorage->isModuleEnabled()
            && $this->configStorage->canCleanQueueTableByCron();
    }
}
