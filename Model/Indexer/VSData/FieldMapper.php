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
use BelSmol\VisualSearch\API\AiManagerInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

/**
 * Class FieldMapper
 * This class is used for mapping field type inside elastic search
 * @package BelSmol\VisualSearch\Model\Indexer\VSData
 */
class FieldMapper implements FieldMapperInterface
{
    /**
     * @param AiManagerInterface $tensorflowManager
     */
    public function __construct(private AiManagerInterface $tensorflowManager)
    {}

    /**
     * Don't use typehint for $attributeCode here
     * @param $attributeCode
     * @param $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = []): string
    {
        $fieldTypes = $this->getAllAttributesTypes($context);
        return isset($fieldTypes[$attributeCode]) ? $attributeCode : '';
    }

    /**
     * Don't use typehint here
     *
     * 'index' => false means that elastic will not consider this field during search
     *
     * @param array $context
     * @return array
     */
    public function getAllAttributesTypes($context = []): array
    {
        return [
            VSDataInterface::FIELD_ENTITY_ID => [
                'type' => 'integer',
                'index' => false,
            ],
            VSDataInterface::FIELD_PRODUCT_ID => [
                'type' => 'integer',
                'index' => false,
            ],
            VSDataInterface::FIELD_VECTOR => [
                'type' => 'dense_vector',
                'index' => true,
                'dims' => $this->tensorflowManager->getCurrentCnnModelVectorDimension(),
                'similarity' => "l2_norm" //also known as Euclidean distance
            ],
        ];
    }
}
