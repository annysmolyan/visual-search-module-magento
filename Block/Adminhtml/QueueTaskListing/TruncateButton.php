<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Block\Adminhtml\QueueTaskListing;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class TruncateButton
 * This button is shown on Queue list UI component in admin panel
 * @package BelSmol\VisualSearch\Block\Adminhtml\QueueTaskListing
 */
class TruncateButton implements ButtonProviderInterface
{
    const BUTTON_LABEL = 'Truncate Log Table';
    const BUTTON_CLASS = 'primary';
    const BUTTON_ORDER = 20;
    const BUTTON_DELETE_CONFIRM_MSG = 'Are you sure you want to do this? This action can not be undone.';

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(protected UrlInterface $urlBuilder)
    {}

    /**
     * Truncate button for QueueTask listing UI component
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __(self::BUTTON_LABEL),
            'class' => self::BUTTON_CLASS,
            'on_click' => $this->getOnClickAction(),
            'sort_order' => self::BUTTON_ORDER,
        ];
    }

    /**
     * @return string
     */
    protected function getOnClickAction(): string
    {
        return 'deleteConfirm(\'' . __(self::BUTTON_DELETE_CONFIRM_MSG) . '\', \'' . $this->getDeleteUrl() . '\')';
    }

    /**
     * @return string
     */
    protected function getDeleteUrl(): string
    {
        return $this->urlBuilder->getUrl('*/*/truncate');
    }
}
