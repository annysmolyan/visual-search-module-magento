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
use BelSmol\VisualSearch\API\SearchRequestManagerInterface;
use BelSmol\VisualSearch\Exception\InvalidSearchInputException;
use BelSmol\VisualSearch\Model\Manager\SearchInputImageManager;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PrepareRequest
 * @package BelSmol\VisualSearch\Controller\Search
 */
class PrepareRequest implements HttpPostActionInterface
{
    protected const PARAM_CATEGORIES = 'categories';
    protected const PARAM_IMAGE_INPUT = 'image';

    protected const EXPLODE_SEPARATOR = ',';

    protected const INDEX_ERROR = "error";
    protected const INDEX_MESSAGE = "message";
    protected const INDEX_SEARCH_REQUEST_PARAM = "search_request_param";

    /**
     * @param RequestInterface $request
     * @param ConfigStorageInterface $configStorage
     * @param JsonFactory $jsonFactory
     * @param SearchRequestManagerInterface $searchRequestManager
     * @param StoreManagerInterface $storeManager
     * @param SearchInputImageManager $searchInputImageManager
     */
    public function __construct(
        protected RequestInterface $request,
        protected ConfigStorageInterface $configStorage,
        protected JsonFactory $jsonFactory,
        protected SearchRequestManagerInterface $searchRequestManager,
        protected StoreManagerInterface $storeManager,
        protected SearchInputImageManager $searchInputImageManager
    ){}

    /**
     * Get request from frontend,
     * save it to temp folder and the image's path to search term table,
     * then return search ID.
     * With that id user will be redirected to the search result page.
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     * @throws InvalidSearchInputException
     * @throws FileSystemException
     */
    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();

        if ($this->isActionAllowed()) {
            $image = $this->request->getParam(self::PARAM_IMAGE_INPUT);
            $processedImage = $this->searchInputImageManager->prepareImage($image);
            $storeId = (int)$this->storeManager->getStore()->getId();
            $categories = $this->getCategoriesFromRequest();

            $searchRequest = $this->searchRequestManager->createSearchRequest(
                $processedImage->getPathInPubMedia(),
                $storeId,
                $categories
            );

            $result->setData([
                self::INDEX_SEARCH_REQUEST_PARAM => $searchRequest->getSearchParamValue(),
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
     * @return array
     */
    protected function getCategoriesFromRequest(): array
    {
        $categories = [];
        $requestData = $this->request->getParam(self::PARAM_CATEGORIES);

        if ($requestData) {
            $categories = explode(self::EXPLODE_SEPARATOR, $requestData);
        }

        return $categories;
    }

    /**
     * @return bool
     */
    protected function isActionAllowed(): bool
    {
        return $this->request->isXmlHttpRequest() && $this->configStorage->isModuleEnabled();
    }
}
