<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Controller\Adminhtml\Queue;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\Model\ResourceModel\QueueTask as QueueTaskResourceModel;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Truncate
 * @package BelSmol\VisualSearch\Controller\Adminhtml\Queue
 */
class Truncate extends Action implements HttpGetActionInterface
{
    protected const TRUNCATE_ACL = 'BelSmol_VisualSearch::truncate_queue_task';

    /**
     * @param ConfigStorageInterface $configStorage
     * @param QueueTaskResourceModel $queueTaskResourceModel
     * @param Context $context
     */
    public function __construct(
        protected ConfigStorageInterface $configStorage,
        protected QueueTaskResourceModel $queueTaskResourceModel,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Truncate queue task table
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        try {
            $this->queueTaskResourceModel->truncateTable();
            $this->messageManager->addSuccessMessage(__('The queue task table has been truncated.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * @return bool
     */
    public function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::TRUNCATE_ACL)
            && $this->configStorage->isModuleEnabled();
    }
}
