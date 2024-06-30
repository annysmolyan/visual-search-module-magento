<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Model\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Version
 * Display module version in admin config
 * @package BelSmol\VisualSearch\Model\Config
 */
class Version extends ConfigValue
{
    private const INITIAL_MODULE_VERSION = "1.0.0";
    private const MODULE_COMPONENT_NAME = "BelSmol_VisualSearch";
    private const COMPOSER_JSON_FILE = "composer.json";

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected Registry $registry,
        protected ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        protected AbstractResource $resource,
        protected AbstractDb $resourceCollection,
        private ComponentRegistrarInterface $componentRegistrar,
        private ReadFactory $readFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @override
     * Display module version in admin config
     * @return AbstractModel
     * @throws FileSystemException|ValidatorException
     */
    public function afterLoad(): AbstractModel
    {
        $version = $this->getModuleVersion();
        $this->setValue($version);
        return $this;
    }

    /**
     * Get current module version from composer.json
     * @return string
     * @throws FileSystemException|ValidatorException
     */
    private function getModuleVersion(): string
    {
        $path = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            self::MODULE_COMPONENT_NAME
        );
        $directoryRead = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile(self::COMPOSER_JSON_FILE);
        $data = json_decode($composerJsonData);

        return (!empty($data->version)) ? $data->version : self::INITIAL_MODULE_VERSION;
    }
}
