<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model;

use BelSmol\VisualSearch\API\Data\SearchInputImageInterface;

/**
 * Class SearchInputImage
 * Is used to manage incoming visual search request image
 * @package BelSmol\VisualSearch\Model
 */
class SearchInputImage implements SearchInputImageInterface
{
    protected string $absolutePath;
    protected string $fileName;
    protected string $extension;
    protected string $base64Content;
    protected string $content;
    protected string $imageUrl;
    protected string $pathInPubMedia;

    /**
     * @return string
     */
    public function getAbsolutePath(): string
    {
        return $this->absolutePath;
    }

    /**
     * @param string $absolutePath
     * @return void
     */
    public function setAbsolutePath(string $absolutePath): void
    {
        $this->absolutePath = $absolutePath;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     * @return void
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getBase64Content(): string
    {
        return $this->base64Content;
    }

    /**
     * @param string $base64Content
     * @return void
     */
    public function setBase64Content(string $base64Content): void
    {
        $this->base64Content = $base64Content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     * @return void
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getPathInPubMedia(): string
    {
        return $this->pathInPubMedia;
    }

    /**
     * @param string $path
     * @return void
     */
    public function setPathInPubMedia(string $path): void
    {
        $this->pathInPubMedia = $path;
    }
}
