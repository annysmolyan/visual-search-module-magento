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
use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\API\QueueTaskManagerInterface;
use BelSmol\VisualSearch\Model\Config\Source\VectorUpdateMode;
use Psr\Log\LoggerInterface;

/**
 * Class VisualSearchImageUpdateCron
 * @package BelSmol\VisualSearch\Cron
 */
class VisualSearchImageUpdateCron
{
    /**
     * @param ConfigStorageInterface $configStorage
     * @param LoggerInterface $logger
     * @param QueueTaskManagerInterface $queueTaskManager
     */
    public function __construct(
        private ConfigStorageInterface $configStorage,
        private LoggerInterface $logger,
        private QueueTaskManagerInterface $queueTaskManager
    ) {}

    /**
     * Update product image vectors by cron
     * When started by cron then module will determine all outdated visual search data rows
     * and re-generate vectors
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->canRunCron()) {
            $this->logger->debug(
                'Cron Job belsmol_vector_upd_cron: Module disabled or "on schedule" mode selected.'
            );
            return;
        }

        $task = $this->queueTaskManager->initEmptyTask();

        $task->setSkus([]);
        $task->setStatus(QueueTaskInterface::STATUS_PENDING);
        $task->setStartedBy(QueueTaskInterface::STARTER_CRON);

        $this->queueTaskManager->pushToQueue($task);

        $this->logger->debug(
            'Cron Job belsmol_vector_upd_cron: a new queue task has been created.'
        );
    }

    /**
     * @return bool
     */
    private function canRunCron(): bool
    {
        return $this->configStorage->isModuleEnabled()
            && $this->configStorage->getVectorUpdateMode() == VectorUpdateMode::VALUE_UPD_BY_CRON;
    }
}
