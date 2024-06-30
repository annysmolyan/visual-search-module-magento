<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Controller\Ajax;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\SearchManagerInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Webapi\Exception;

/**
 * Class SearchCategoryList
 * @package BelSmol\VisualSearch\Controller\Ajax
 */
class SearchCategoryList implements HttpGetActionInterface
{
    protected const PARAM_TERM = 'term';

    protected const INDEX_RESULTS = "results";
    protected const INDEX_PAGINATION = "pagination";
    protected const INDEX_ERROR = "error";
    protected const INDEX_MESSAGE = "message";

    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param SearchManagerInterface $searchManager
     * @param ConfigStorageInterface $configStorage
     */
    public function __construct(
        protected RequestInterface $request,
        protected JsonFactory $jsonFactory,
        protected SearchManagerInterface $searchManager,
        protected ConfigStorageInterface $configStorage
    ) {}

    /**
     * If category selection allowed,
     * return category list according to a search term
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();

        if ($this->isActionAllowed()) {
            $searchTerm = $this->request->getParam(self::PARAM_TERM);
            $categories = $this->getCategories($searchTerm);
            $result->setData([
                self::INDEX_RESULTS => $categories,
                self::INDEX_PAGINATION => ["more" => false]
            ]);
        } else {
            $result->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
            $result->setData([
                self::INDEX_ERROR => true,
                self::INDEX_MESSAGE => 'Invalid request'
            ]);
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isActionAllowed(): bool
    {
        return $this->request->isAjax()
            && $this->configStorage->isModuleEnabled()
            && $this->configStorage->isEnabledCategorySelection();
    }


    /**
     * @param string $searchTerm
     * @return array
     */
    protected function getCategories(string $searchTerm): array
    {
        $categoryCollection = $this->searchManager->getSearchCategoryCollection();
        $categoryCollection->addFieldToFilter(CategoryInterface::KEY_NAME, ['like' => '%'. $searchTerm. '%']);
        $categories = $categoryCollection->getItems();
        $data = [];

        foreach ($categories as $category) {
            $data[] = [
                "id" => $category->getId(),
                "text" => $category->getName()
            ];
        }

        return $data;
    }
}
