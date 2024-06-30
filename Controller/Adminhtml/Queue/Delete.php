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
use BelSmol\VisualSearch\API\QueueTaskRepositoryInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 * @package BelSmol\VisualSearch\Controller\Adminhtml\Queue
 */
class Delete extends Action implements HttpGetActionInterface
{
    protected const REQUEST_ID_PARAM_NAME = 'id';
    protected const DELETE_ACL = 'BelSmol_VisualSearch::delete_queue_task';

    /**
     * @param ConfigStorageInterface $configStorage
     * @param QueueTaskRepositoryInterface $queueTaskRepository
     * @param Context $context
     */
    public function __construct(
        protected ConfigStorageInterface $configStorage,
        protected QueueTaskRepositoryInterface $queueTaskRepository,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Delete a specific queue task
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam(self::REQUEST_ID_PARAM_NAME);

        try {
            $this->queueTaskRepository->deleteById((int) $id);
            $this->messageManager->addSuccessMessage(__('The queue task with id %1 has been deleted.', $id));
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
        return $this->_authorization->isAllowed(self::DELETE_ACL)
            && $this->configStorage->isModuleEnabled();
    }
}
