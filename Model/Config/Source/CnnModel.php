<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Config\Source;

use BelSmol\VisualSearch\API\AiManagerInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CnnModel
 * @package BelSmol\VisualSearch\Model\Config\Source
 */
class CnnModel implements OptionSourceInterface
{
    protected const INDEX_VALUE = "value";
    protected const INDEX_LABEL = "label";

    /**
     * @param AiManagerInterface $tensorflowManager
     */
    public function __construct(protected AiManagerInterface $tensorflowManager)
    {}

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $cnnModels = $this->tensorflowManager->getCnnModelsList();
        $optionArray = [];

        foreach ($cnnModels as $cnn) {
            $optionArray[] = [
                self::INDEX_VALUE => $cnn,
                self::INDEX_LABEL => $cnn,
            ];
        }

        return $optionArray;
    }
}
