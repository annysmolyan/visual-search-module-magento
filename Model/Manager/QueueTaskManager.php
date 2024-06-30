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

use BelSmol\VisualSearch\API\Data\QueueTaskInterface;
use BelSmol\VisualSearch\API\Data\QueueTaskInterfaceFactory;
use BelSmol\VisualSearch\API\QueueTaskManagerInterface;
use BelSmol\VisualSearch\API\QueueTaskRepositoryInterface;
use Exception;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Class QueueTaskManager
 * @package BelSmol\VisualSearch\Model\Manager
 */
class QueueTaskManager implements QueueTaskManagerInterface
{
    const TOPIC_NAME = 'vsdataVectorChangeTopic'; //from communication.xml

    /**
     * @param QueueTaskInterfaceFactory $queueTaskFactory
     * @param PublisherInterface $publisher
     * @param QueueTaskRepositoryInterface $queueTaskRepository
     */
    public function __construct(
        protected QueueTaskInterfaceFactory $queueTaskFactory,
        protected PublisherInterface $publisher,
        protected QueueTaskRepositoryInterface $queueTaskRepository
    ) {}

    /**
     * @return QueueTaskInterface
     */
    public function initEmptyTask(): QueueTaskInterface
    {
        return $this->queueTaskFactory->create();
    }

    /**
     * @param QueueTaskInterface $task
     * @return QueueTaskInterface
     * @throws Exception
     */
    public function pushToQueue(QueueTaskInterface $task): QueueTaskInterface
    {
        $this->queueTaskRepository->save($task);
        $this->publisher->publish(self::TOPIC_NAME, $task);

        return $task;
    }
}
