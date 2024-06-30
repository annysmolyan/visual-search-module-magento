<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Controller\Search;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Result
 * @package BelSmol\VisualSearch\Controller\Search
 */
class Result implements HttpGetActionInterface
{
    protected const PAGE_TITLE = "Visual Search Result";

    /**
     * @param ConfigStorageInterface $configStorage
     * @param PageFactory $pageFactory
     */
    public function __construct(
        protected ConfigStorageInterface $configStorage,
        protected PageFactory $pageFactory
    ) {}

    /**
     * Show visual search results
     * @return ResultInterface
     * @throws NotFoundException
     */
    public function execute(): ResultInterface
    {
        if (!$this->isActionAllowed()) {
            throw new NotFoundException(__("Page not found."));
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__(self::PAGE_TITLE));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function isActionAllowed(): bool
    {
        return $this->configStorage->isModuleEnabled();
    }
}
