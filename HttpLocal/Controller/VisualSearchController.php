<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\HttpLocal\Controller;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\Data\HttpLocalDTOResponseVisualSearchInterface;
use BelSmol\VisualSearch\API\Data\HttpLocalDTOResponseVisualSearchInterfaceFactory;
use BelSmol\VisualSearch\API\HttpLocalVisualSearchControllerInterface;
use BelSmol\VisualSearch\API\SearchServiceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\RequestInterface;

/**
 * Class VisualSearchController
 * Visual search API controller
 * @package BelSmol\VisualSearch\HttpLocal\Controller
 */
class VisualSearchController implements HttpLocalVisualSearchControllerInterface
{
    protected const PARAM_CATEGORIES = 'categories';
    protected const PARAM_IMAGE_INPUT = 'image';
    protected const PARAM_PAGE = 'page';
    protected const EXPLODE_SEPARATOR = ',';

    /**
     * @param RequestInterface $request
     * @param SearchServiceInterface $searchService
     * @param HttpLocalDTOResponseVisualSearchInterfaceFactory $dtoSearchResponseFactory
     * @param ConfigStorageInterface $configStorage
     */
    public function __construct(
        protected RequestInterface $request,
        protected SearchServiceInterface $searchService,
        protected HttpLocalDTOResponseVisualSearchInterfaceFactory $dtoSearchResponseFactory,
        protected ConfigStorageInterface $configStorage
    ){}

    /**
     * @return HttpLocalDTOResponseVisualSearchInterface
     */
    public function search(): HttpLocalDTOResponseVisualSearchInterface
    {
        $base64ImgData = $this->request->getParam(self::PARAM_IMAGE_INPUT, '');
        $categoryIds = $this->getCategoriesFromRequest();
        $searchResult = $this->searchService->search($base64ImgData, $categoryIds);

        return $this->createSearchResponse($searchResult);
    }

    /**
     * @param ProductCollection $collection
     * @return HttpLocalDTOResponseVisualSearchInterface
     */
    protected function createSearchResponse(ProductCollection $collection): HttpLocalDTOResponseVisualSearchInterface
    {
        $totalCount = $collection->getSize();
        $currentPage = $this->request->getParam(self::PARAM_PAGE, 1);
        $pageSize = $this->configStorage->getApiProductPageSize();

        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);

        $totalPages = $collection->getLastPageNumber();

        return $this->dtoSearchResponseFactory->create([
           'totalCount' => $totalCount,
           'totalPages' => $totalPages,
           'currentPage' => (int)$currentPage,
           'products' => $currentPage <= $totalPages ? $collection->getItems() : [],
        ]);
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
}
