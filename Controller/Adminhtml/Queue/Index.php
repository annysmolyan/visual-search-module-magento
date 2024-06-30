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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package BelSmol\VisualSearch\Controller\Adminhtml\Queue
 */
class Index extends Action implements HttpGetActionInterface
{
    protected const INDEX_ACL = 'BelSmol_VisualSearch::index_queue_task';
    protected const ACTIVE_MENU = 'BelSmol_VisualSearch::main';
    protected const PAGE_TITLE = 'Queue List';

    /**
     * @param PageFactory $resultPageFactory
     * @param ConfigStorageInterface $configStorage
     * @param Context $context
     */
    public function __construct(
        protected PageFactory $resultPageFactory,
        protected ConfigStorageInterface $configStorage,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Show listing of queue tasks
     * @return ResultInterface
     * @throws NotFoundException
     */
    public function execute(): ResultInterface
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__(self::PAGE_TITLE)));
        $resultPage->setActiveMenu(self::ACTIVE_MENU);

        return $resultPage;
    }

    /**
     * @return bool
     */
    public function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::INDEX_ACL)
            && $this->configStorage->isModuleEnabled();
    }
}
