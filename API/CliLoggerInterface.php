<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */
declare(strict_types=1);

namespace BelSmol\VisualSearch\API;

/**
 * @api
 * Interface CliLoggerInterface
 * Print colored messages in terminal
 * @package BelSmol\VisualSearch\API
 */
interface CliLoggerInterface
{
    /**
     * Print red text
     * @param string $message
     * @return void
     */
    public function printError(string $message): void;

    /**
     * Print yellow text
     * @param string $message
     * @return void
     */
    public function printWarning(string $message): void;

    /**
     * Print green message
     * @param string $message
     * @return void
     */
    public function printSuccess(string $message): void;

    /**
     * Print white message
     * @param string $message
     * @return void
     */
    public function printMessage(string $message): void;

    /**
     * @param int $done
     * @param int $total
     * @return void
     */
    public function printProgressBar(int $done, int $total): void;
}
