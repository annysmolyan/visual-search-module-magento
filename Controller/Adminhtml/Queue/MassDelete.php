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
use BelSmol\VisualSearch\Model\ResourceModel\QueueTask\CollectionFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @package BelSmol\VisualSearch\Controller\Adminhtml\Queue
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    protected const DELETE_ACL = 'BelSmol_VisualSearch::delete_queue_task';

    /**
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param QueueTaskRepositoryInterface $queueTaskRepository
     * @param ConfigStorageInterface $configStorage
     * @param Context $context
     */
    public function __construct(
        protected Filter $filter,
        protected CollectionFactory $collectionFactory,
        protected QueueTaskRepositoryInterface $queueTaskRepository,
        protected ConfigStorageInterface $configStorage,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Mass delete action for queue task
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $itemsCount = 0;

            foreach ($collection as $item) {
                $this->queueTaskRepository->delete($item);
                $itemsCount++;
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 queue task(s) have been deleted.', $itemsCount)
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
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
