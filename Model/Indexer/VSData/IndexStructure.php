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

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * Class IndexStructure
 * Created according to \Magento\Elasticsearch\Model\Indexer\IndexStructure
 * Created own class because of $adapter argument.
 * Need to use own adapter to perform create/delete operation
 * @package BelSmol\VisualSearch\Model\Indexer\VSData
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @param ElasticsearchAdapter $adapter
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        protected ElasticsearchAdapter $adapter,
        protected ScopeResolverInterface $scopeResolver
    ) {}

    /**
     * Don't use type hint for $indexerId
     * @param $indexerId
     * @param array $dimensions
     * @return void
     */
    public function delete(
        $indexerId,
        array $dimensions = []
    ): void {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->cleanIndex($scopeId, $indexerId);
    }

    /**
     * Don't use type hint for $indexerId
     * @param $indexerId
     * @param array $fields
     * @param array $dimensions
     * @return void
     */
    public function create(
        $indexerId,
        array $fields,
        array $dimensions = []
    ): void {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->checkIndex($scopeId, $indexerId, false);
    }
}
