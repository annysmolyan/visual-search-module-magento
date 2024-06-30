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

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;

/**
 * Class BatchDataMapper
 * Map visual_search_data data for indexer, remove unnecessary attributes, clear data and so on
 * @package BelSmol\VisualSearch\Model\Indexer\VSData
 */
class BatchDataMapper implements BatchDataMapperInterface
{
    private const STORE_ID_FIELD = 'store_id';

    /**
     * @param Builder $builder
     * @param FieldMapper $fieldMapper
     */
    public function __construct(
        private Builder $builder,
        private FieldMapper $fieldMapper
    ) {}

    /**
     * DON'T USE STRICT TYPE FOR STORE_ID
     * Map index data for using in search engine metadata
     * Added store_id here to the field mapping
     * @param array $documentData
     * @param $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = []): array
    {
        $documents = [];

        foreach ($documentData as $entityId => $indexData) {
            $this->builder->addField(self::STORE_ID_FIELD, $storeId);
            foreach ($indexData as $attributeCode => $value) {
                if ($fieldName = $this->fieldMapper->getFieldName($attributeCode, $context)) {
                    $this->builder->addField($fieldName, $value);
                }
            }
            $documents[$entityId] = $this->builder->build();
        }

        return $documents;
    }
}
