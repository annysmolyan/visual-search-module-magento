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
use BelSmol\VisualSearch\API\ElasticsearchClientInterface;
use Exception;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Webapi\Response;
use stdClass;

/**
 * Class Client
 * Make calls to elasticsearch
 * @package BelSmol\VisualSearch\Model\Elasticsearch
 */
class Client implements ElasticsearchClientInterface
{
    /**
     * @param CurlFactory $curlFactory
     * @param ConfigStorageInterface $configStorage
     */
    public function __construct(
        protected CurlFactory $curlFactory,
        protected ConfigStorageInterface $configStorage
    ) {}

    /**
     * @param ElasticsearchRequestInterface $request
     * @return stdClass
     * @throws Exception
     */
    public function call(ElasticsearchRequestInterface $request): stdClass
    {
        $this->validateElasticsearchVersion();

        $curl = $this->curlFactory->create();

        $curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $curl->addHeader('Content-Type',  'application/json');
        $curl->addHeader('Content-Length',  strlen($request->toJson()));

        $curl->post($request->getEndpoint(), $request->toJson());

        if ((int)$curl->getStatus() != Response::HTTP_OK) {
            throw new Exception($curl->getBody());
        }

        return json_decode($curl->getBody());
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function validateElasticsearchVersion(): void
    {
        if (!$this->configStorage->isElasticVersionCorrect()) {
            throw new Exception("Invalid Elasticsearch version.");
        }
    }
}
