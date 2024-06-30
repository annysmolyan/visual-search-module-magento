<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Observer;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\API\QueueTaskManagerInterface;
use BelSmol\VisualSearch\Model\Config\Source\VectorUpdateMode;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class UpdateProductImageVectorObserver
 * @package BelSmol\VisualSearch\Observer
 */
class UpdateProductImageVectorObserver implements ObserverInterface
{
    /**
     * @param ConfigStorageInterface $configStorage
     * @param ManagerInterface $messageManager
     * @param QueueTaskManagerInterface $queueTaskManager
     */
    public function __construct(
        private ConfigStorageInterface $configStorage,
        private ManagerInterface $messageManager,
        private QueueTaskManagerInterface $queueTaskManager
    ) {}

    /**
     * Every time when a product is getting updated, create a queue task to check small_image changes,
     * if the image was changed then build a new vector and save it
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->isObserverAllowed()) {
            return;
        }

        $product = $observer->getProduct();

        $task = $this->queueTaskManager->initEmptyTask();
        $task->setSkus([$product->getSku()]);
        $task->setStatus(QueueTaskInterface::STATUS_PENDING);
        $task->setStartedBy(QueueTaskInterface::STARTER_SAVE_ACTION);

        $this->queueTaskManager->pushToQueue($task);

        $this->messageManager->addSuccessMessage(
            __("Created a new queue for the small_image vector update")
        );
    }

    /**
     * @return bool
     */
    private function isObserverAllowed(): bool
    {
        return $this->configStorage->isModuleEnabled()
            && $this->configStorage->getVectorUpdateMode() == VectorUpdateMode::VALUE_UPD_ON_SAVE;
    }
}
