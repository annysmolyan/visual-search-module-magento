<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Exception;

use Magento\Framework\Exception\InputException;

/**
 * Class ModuleDisabledException
 * @package BelSmol\VisualSearch\Exception
 */
class ModuleDisabledException extends InputException
{
    /**
     * @OVERRIDE
     * @var string
     */
    protected $message = "Module disabled";

    /**
     * @OVERRIDE
     * @var int
     */
    protected $code = 400;
}