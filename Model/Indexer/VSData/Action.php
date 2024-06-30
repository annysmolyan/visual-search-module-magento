<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Indexer\VSData;

use BelSmol\VisualSearch\API\Data\VSDataInterface;
use BelSmol\VisualSearch\API\VSDataRepositoryInterface;
use Generator;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

/**
 * Class Action
 * This class is used to build data for index
 * Provides iterator through number of visual search data suitable for indexation
 * @package BelSmol\VisualSearch\Model\Indexer\VSData
 */
class Action
{
    protected const LAST_ENTITY_ID = 0;

    /**
     * @param VSDataRepositoryInterface $vsDataRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        private VSDataRepositoryInterface $vsDataRepository,
        private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {}

    /**
     * Get visual search data for a store id in indexer format
     *
     * @param int $storeId
     * @param array|null $vsDataEntityIds
     * @return Generator
     */
    public function rebuildStoreIndex(int $storeId, array $vsDataEntityIds = null): Generator
    {
        $lastEntityId = self::LAST_ENTITY_ID;

        do {
            $vsData = $this->getSearchableVsData($storeId, $lastEntityId, $vsDataEntityIds);

            foreach ($vsData as $data) {
                $lastEntityId = (int) $data[VSDataInterface::FIELD_ENTITY_ID];
                yield $lastEntityId => $data;
            }

        } while (!empty($vsData));
    }

    /**
     * @param int $storeId
     * @param int $lastEntityId
     * @param array|null $entityIds
     * @return array
     */
    protected function getSearchableVsData(int $storeId, int $lastEntityId, array $entityIds = null): array
    {
        $result = [];

        if ($lastEntityId > 0) {
            return $result;
        }

        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter(VSDataInterface::FIELD_STORE_ID, $storeId);

        if ($entityIds) {
            $searchCriteriaBuilder->addFilter(VSDataInterface::FIELD_ENTITY_ID, $entityIds, 'in');
        }

        $criteria = $searchCriteriaBuilder->create();
        $vsDataList = $this->vsDataRepository->getList($criteria)->getItems();

        foreach ($vsDataList as $key => $data) {
            $result[$key][VSDataInterface::FIELD_ENTITY_ID] = (int)$data->getId();
            $result[$key][VSDataInterface::FIELD_PRODUCT_ID] = $data->getProductId();
            $result[$key][VSDataInterface::FIELD_VECTOR] = $data->getVector();
        }

        return $result;
    }
}
