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

use BelSmol\VisualSearch\API\CliLoggerInterface;

/**
 * Class CliLogger
 * Print colored messages in terminal
 * @package BelSmol\VisualSearch\Model\Logger
 */
class CliLogger implements CliLoggerInterface
{
    /**
     * Print red text
     * @param string $message
     * @return void
     */
    public function printError(string $message): void
    {
        echo "\e[31m $message \e[0m\n";
    }

    /**
     * Print yellow text
     * @param string $message
     * @return void
     */
    public function printWarning(string $message): void
    {
        echo "\e[33m $message \e[0m\n";
    }

    /**
     * Print green message
     * @param string $message
     * @return void
     */
    public function printSuccess(string $message): void
    {
        echo "\e[32m $message \e[0m\n";
    }

    /**
     * Print white message
     * @param string $message
     * @return void
     */
    public function printMessage(string $message): void
    {
        echo $message . "\n";
    }

    /**
     * Print progress bar
     * @param int $done
     * @param int $total
     * @return void
     */
    public function printProgressBar(int $done, int $total): void
    {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }
}
