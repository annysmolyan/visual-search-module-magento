<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model;

use BelSmol\VisualSearch\API\Data\HttpRemoteDTORequestInterface;
use BelSmol\VisualSearch\API\Data\HttpRemoteDTORequestInterfaceFactory;
use BelSmol\VisualSearch\API\Data\HttpRemoteDTOResponseInterface;
use BelSmol\VisualSearch\API\HttpRemoteClientInterface;
use BelSmol\VisualSearch\API\AiManagerInterface;
use BelSmol\VisualSearch\API\VectorGeneratorInterface;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Profiler;
use Magento\Framework\Webapi\Response;

/**
 * Class VectorGenerator
 * Get generated image vector
 * @package BelSmol\VisualSearch\Model
 */
class VectorGenerator implements VectorGeneratorInterface
{
    public const VECTOR_CSV_FOLDER = 'visual_search'; //don't change! can break AI app

    protected const CSV_HEADER_IMAGE = 'image_pub_media_path'; // don't change! can break AI app
    protected const CSV_HEADER_VECTOR = 'vector'; // don't change! can break AI app

    protected const CSV_IMAGE_INDEX = 0;
    protected const CSV_VECTOR_INDEX = 1;

    protected const PRODUCT_IMAGES_FOLDER = 'catalog/product';

    protected WriteInterface $varDirectory;

    /**
     * @param HttpRemoteClientInterface $httpClient
     * @param HttpRemoteDTORequestInterfaceFactory $dtoRequestFactory
     * @param AiManagerInterface $aiManager
     * @param Csv $csvProcessor
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        protected HttpRemoteClientInterface $httpClient,
        protected HttpRemoteDTORequestInterfaceFactory $dtoRequestFactory,
        protected AiManagerInterface $aiManager,
        protected Csv $csvProcessor,
        Filesystem $filesystem
    ) {
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Use only for search request by customers on FE side.
     * Don't execute this for multiple images!
     *
     * @param string $imagePubMediaPath
     * @return array
     * @throws Exception
     */
    public function generateSearchImageVector(string $imagePubMediaPath): array
    {
        Profiler::start('BelSmol_VisualSearch:' . __METHOD__);

        $request = $this->dtoRequestFactory->create([
            'endpoint' => $this->aiManager->getSingleImageExtractorEndpoint(),
            'body' => [
                'modelName' => $this->aiManager->getCurrentCnnModel(),
                'path' => $imagePubMediaPath
            ]
        ]);

        $response = $this->call($request);
        $data = $response->getData();

        Profiler::stop('BelSmol_VisualSearch:' . __METHOD__);

        return $data['vector'] ?? [];
    }

    /**
     * WARNING! For the sake of memory limits, vector will be returned as json string.
     *
     * @param array $imagePaths
     * @return array
     * @throws FileSystemException
     */
    public function generateProductsVectors(array $imagePaths): array
    {
        $this->varDirectory->create(self::VECTOR_CSV_FOLDER);

        $csvFileName = date('Y-m-d_H:m:s').'.csv';
        $this->generateVectorCsvFile($csvFileName, $imagePaths);

        $request = $this->dtoRequestFactory->create([
            'endpoint' => $this->aiManager->getCsvExtractorEndpoint(),
            'body' => [
                'modelName' => $this->aiManager->getCurrentCnnModel(),
                'csvFileName' => $csvFileName
            ]
        ]);

        $this->call($request);

        return $this->getVectorsFromCsv($csvFileName);
    }

    /**
     * @param string $csvFileName
     * @param array $images
     * @return void
     * @throws FileSystemException
     */
    protected function generateVectorCsvFile(string $csvFileName, array $images): void
    {
        $csvFilePath = $this->getCsvAbsolutePath($csvFileName);

        $data = [[self::CSV_HEADER_IMAGE, self::CSV_HEADER_VECTOR]]; //generate header

        foreach ($images as $path) {
            $data[] = [self::PRODUCT_IMAGES_FOLDER . $path, null]; // product image in pub media
        }

        $this->csvProcessor->appendData($csvFilePath, $data);
    }

    /**
     * @param string $csvFileName
     * @return array
     * @throws Exception
     */
    protected function getVectorsFromCsv(string $csvFileName): array
    {
        $csvFilePath = $this->getCsvAbsolutePath($csvFileName);
        $csvData = $this->csvProcessor->getData($csvFilePath);

        $result = [];

        foreach ($csvData as $rowIndex => $data) {
            if ($rowIndex == 0) { //skip the first row
                continue;
            }

            $image = str_replace(self::PRODUCT_IMAGES_FOLDER, "", $data[self::CSV_IMAGE_INDEX]);
            $result[$image] = $data[self::CSV_VECTOR_INDEX]; //return json string to save memory
        }

        return $result;
    }

    /**
     * @param HttpRemoteDTORequestInterface $request
     * @return HttpRemoteDTOResponseInterface
     * @throws Exception
     */
    protected function call(HttpRemoteDTORequestInterface $request): HttpRemoteDTOResponseInterface
    {
        $response = $this->httpClient->call($request);

        if ($response->getStatus() != Response::HTTP_OK) {
            throw new Exception(json_encode($response->getData()));
        }

        return $response;
    }

    /**
     * @param string $csvFileName
     * @return string
     */
    protected function getCsvPathInVarDir(string $csvFileName): string
    {
        return self::VECTOR_CSV_FOLDER . DIRECTORY_SEPARATOR . $csvFileName;
    }

    /**
     * @param string $csvFileName
     * @return string
     */
    protected function getCsvAbsolutePath(string $csvFileName): string
    {
        $pathInVarDir = $this->getCsvPathInVarDir($csvFileName);
        return $this->varDirectory->getAbsolutePath($pathInVarDir);
    }
}
