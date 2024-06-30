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

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class VectorUpdateMode
 * Return vector update mode for config
 * @package BelSmol\VisualSearch\Model\Config\Source
 */
class VectorUpdateMode implements OptionSourceInterface
{
    protected const INDEX_VALUE = "value";
    protected const INDEX_LABEL = "label";

    protected const LABEL_UPD_ON_SAVE = "Update on Product Save";
    protected const LABEL_UPD_BY_CRON = "Update by Cron";

    public const VALUE_UPD_ON_SAVE = "on_save";
    public const VALUE_UPD_BY_CRON = "by_cron";

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                self::INDEX_LABEL => __(self::LABEL_UPD_ON_SAVE),
                self::INDEX_VALUE => self::VALUE_UPD_ON_SAVE
            ],
            [
                self::INDEX_LABEL => __(self::LABEL_UPD_BY_CRON),
                self::INDEX_VALUE => self::VALUE_UPD_BY_CRON
            ],
        ];
    }
}
