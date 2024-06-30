<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Elasticsearch;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\Data\ElasticsearchRequestInterface;
use BelSmol\VisualSearch\API\Data\ElasticsearchRequestInterfaceFactory;
use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\API\ElasticsearchRequestBuilderInterface;
use BelSmol\VisualSearch\Model\Indexer\VSData\Indexer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DTORequestBuilder
 * Build requests for elasticsearch
 * @package BelSmol\VisualSearch\Model\Elasticsearch
 */
class DTORequestBuilder implements ElasticsearchRequestBuilderInterface
{
    /**
     * @param ElasticsearchRequestInterfaceFactory $requestFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigStorageInterface $configStorage
     */
    public function __construct(
        protected ElasticsearchRequestInterfaceFactory $requestFactory,
        protected StoreManagerInterface $storeManager,
        protected ConfigStorageInterface $configStorage
    ) {}

    /**
     * @param array $vector
     * @return ElasticsearchRequestInterface
     * @throws NoSuchEntityException
     */
    public function buildKnnSearchRequest(array $vector): ElasticsearchRequestInterface

    {
        return $this->requestFactory->create(
            [
                "endpoint" => $this->getVisualSearchDataElasticIndexerEndpoint(),
                "body" => [
                    "knn" => [
                        "field" => VSDataInterface::FIELD_VECTOR,
                        "query_vector" => $vector,
                        "k" => 100, //todo set max search result in admin
                        "num_candidates" => 100, //todo set max search result in admin + set similarity index percentage
                    ],
                    "_source" => false, //don't load indexer data to improve performance
                    "size" => $this->configStorage->getSearchItemsCount(),
                    "fields" => [VSDataInterface::FIELD_VECTOR],
                    "min_score" => $this->configStorage->getMinRelevanceScope(),
                ]
            ]
        );
    }

    /**
     * @return string
     */
    protected function getElasticsearchUrl(): string
    {
        return $this->configStorage->getElasticsearchHost() . ':' . $this->configStorage->getElasticsearchPort() . '/';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getVisualSearchDataElasticIndexerEndpoint(): string
    {
        return sprintf(
            '%s%s_%s_%s/_search',
            $this->getElasticsearchUrl(),
            $this->configStorage->getElasticsearchIndexPrefix(),
            Indexer::INDEXER_ID,
            $this->storeManager->getStore()->getId(),
        );
    }
}
