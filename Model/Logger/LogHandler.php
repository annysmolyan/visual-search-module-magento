<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;

/**
 * Class LogHandler
 * Custom logger here
 * @package BelSmol\VisualSearch\Model\Logger
 */
class LogHandler extends Base
{
    protected const FILE_PATH = '/var/log/visual_search.log';

    /**
     * @OVERRIDE
     * Logging level
     * @var int
     */
    protected $loggerType = MonologLogger::WARNING;

    /**
     * @OVERRIDE
     * File name
     * @var string
     */
    protected $fileName = self::FILE_PATH;
}
