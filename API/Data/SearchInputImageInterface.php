<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */
declare(strict_types=1);

namespace BelSmol\VisualSearch\API\Data;

/**
 * @api
 * Interface SearchInputImageInterface
 * Is used to manage incoming visual search request image
 * @package BelSmol\VisualSearch\API\Data
 */
interface SearchInputImageInterface
{
    /**
     * @return string
     */
    public function getAbsolutePath(): string;

    /**
     * @param string $absolutePath
     * @return void
     */
    public function setAbsolutePath(string $absolutePath): void;

    /**
     * @return string
     */
    public function getFileName(): string;

    /**
     * @param string $fileName
     * @return void
     */
    public function setFileName(string $fileName): void;

    /**
     * @return string
     */
    public function getExtension(): string;

    /**
     * @param string $extension
     * @return void
     */
    public function setExtension(string $extension): void;

    /**
     * @return string
     */
    public function getBase64Content(): string;

    /**
     * @param string $base64Content
     * @return void
     */
    public function setBase64Content(string $base64Content): void;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void;

    /**
     * @return string
     */
    public function getImageUrl(): string;

    /**
     * @param string $imageUrl
     * @return void
     */
    public function setImageUrl(string $imageUrl): void;

    /**
     * @return string
     */
    public function getPathInPubMedia(): string;

    /**
     * @param string $path
     * @return void
     */
    public function setPathInPubMedia(string $path): void;
}
