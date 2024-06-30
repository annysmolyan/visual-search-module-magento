<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Elasticsearch\Request;

use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\API\ElasticsearchKnnRequestInterface;
use BelSmol\VisualSearch\API\ElasticsearchRequestBuilderInterface;
use BelSmol\VisualSearch\Model\Elasticsearch\Client;
use BelSmol\VisualSearch\Model\ResourceModel\VSData\Collection as VisualSearchDataCollection;
use BelSmol\VisualSearch\Model\ResourceModel\VSData\CollectionFactory as VisualSearchDataCollectionFactory;
use Exception;
use stdClass;

/**
 * Class KnnRequest
 * Make knn search (search by vector)
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/knn-search.html
 * @package BelSmol\VisualSearch\Model\Elasticsearch\Search
 */
class KnnRequest implements ElasticsearchKnnRequestInterface
{
    /**
     * @param ElasticsearchRequestBuilderInterface $elasticsearchRequestBuilder
     * @param Client $elasticSearchClient
     * @param VisualSearchDataCollectionFactory $visualSearchCollectionFactory
     */
    public function __construct(
        protected ElasticsearchRequestBuilderInterface $elasticsearchRequestBuilder,
        protected Client $elasticSearchClient,
        protected VisualSearchDataCollectionFactory $visualSearchCollectionFactory
    ) {}

    /**
     * Make a search by giving product image vector,
     * find similar images in index
     * @param array $vector
     * @return VisualSearchDataCollection
     * @throws Exception
     */
    public function search(array $vector): VisualSearchDataCollection
    {
        $request = $this->elasticsearchRequestBuilder->buildKnnSearchRequest($vector);
        $response = $this->elasticSearchClient->call($request);

        return $this->getVsDataCollection($response);
    }

    /**
     * @param stdClass $response
     * @return VisualSearchDataCollection
     */
    protected function getVsDataCollection(stdClass $response):VisualSearchDataCollection
    {
        $ids = [];

        if ($response->hits->total->value > 0) {
            foreach ($response->hits->hits as $hit) {
                $ids[] = $hit->_id;
            }
        }

        $collection = $this->visualSearchCollectionFactory->create();
        $collection->getSelect()->where(VSDataInterface::FIELD_ENTITY_ID . " IN (?)", $ids);

        return $collection;
    }
}
