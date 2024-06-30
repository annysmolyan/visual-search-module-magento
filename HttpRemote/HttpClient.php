<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\HttpRemote;

use BelSmol\VisualSearch\API\Data\HttpRemoteDTORequestInterface;
use BelSmol\VisualSearch\API\Data\HttpRemoteDTOResponseInterface;
use BelSmol\VisualSearch\API\HttpRemoteDTOResponseMapperInterface;
use BelSmol\VisualSearch\API\HttpRemoteClientInterface;
use Exception;
use Magento\Framework\HTTP\Client\CurlFactory;

/**
 * Class HttpClient
 * External api call here
 * @package BelSmol\VisualSearch\HttpRemote
 */
class HttpClient implements HttpRemoteClientInterface
{
    /**
     * @param CurlFactory $curlFactory
     * @param HttpRemoteDTOResponseMapperInterface $dtoResponseMapper
     */
    public function __construct(
        protected CurlFactory $curlFactory,
        protected HttpRemoteDTOResponseMapperInterface $dtoResponseMapper
    ) {}

    /**
     * Make an api call and return a response
     * @param HttpRemoteDTORequestInterface $request
     * @return HttpRemoteDTOResponseInterface
     * @throws Exception
     */
    public function call(HttpRemoteDTORequestInterface $request): HttpRemoteDTOResponseInterface
    {
        $curl = $this->curlFactory->create();

        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 5);
        $curl->setOption(CURLOPT_TIMEOUT, 300);
        $curl->addHeader('Content-Type',  'application/json');

        $curl->post($request->getEndpoint(), json_encode($request->getBody()));

        $statusCode = (int)$curl->getStatus();

        try {
            $responseBody = json_decode($curl->getBody());
        } catch (Exception $exception) {
            $responseBody = ['message' => $curl->getBody()];
        }

        return $this->dtoResponseMapper->map($statusCode, (array)$responseBody);
    }
}
