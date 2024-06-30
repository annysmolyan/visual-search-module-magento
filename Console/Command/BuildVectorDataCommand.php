<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Console\Command;

use BelSmol\VisualSearch\API\ConfigStorageInterface;
use BelSmol\VisualSearch\API\VSDataManagerInterface;
use BelSmol\VisualSearch\Model\Indexer\VSData\Indexer;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildVectorDataCommand
 * Build visual search data from scratch
 * @package BelSmol\VisualSearch\Console\Command
 */
class BuildVectorDataCommand extends Command
{
    private const COMMAND_NAME = "visual-search:vector-data:build";
    private const COMMAND_DESCRIPTION = "Create visual search data. High-load operation.";

    /**
     * @param State $state
     * @param ConfigStorageInterface $configStorage
     * @param VSDataManagerInterface $searchDataManager
     * @param string|null $name
     */
    public function __construct(
        private State $state,
        private ConfigStorageInterface $configStorage,
        private VSDataManagerInterface $searchDataManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @OVERRIDE
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(self::COMMAND_DESCRIPTION);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);

        if (!$this->configStorage->isModuleEnabled()) {
            $output->writeln(sprintf('<error>%s</error>', 'Please, enable module first'));
            return self::FAILURE;
        }

        $output->writeln(sprintf('<info>%s</info>', 'Image vector generation start'));

        try {
            $this->searchDataManager->createFullCatalogVisualSearchData();
            $this->resetVisualSearchIndex($output);
            $this->reindexVisualSearchIndex($output);
        } catch (Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return self::FAILURE;
        }

        $output->writeln(sprintf('<info>%s</info>', 'Image vector generation has been finished'));

        return self::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @return void
     * @throws ExceptionInterface
     */
    private function resetVisualSearchIndex(OutputInterface $output): void
    {
        $output->writeln(__('Reset visual search data index'));

        $arguments = new ArrayInput([
            'command' => 'indexer:reset',
            'index' => [Indexer::INDEXER_ID]
        ]);

        $this->getApplication()->find('indexer:reset')->run($arguments, $output);
    }

    /**
     * @param OutputInterface $output
     * @return void
     * @throws ExceptionInterface
     */
    private function reindexVisualSearchIndex(OutputInterface $output): void
    {
        $output->writeln(__('Start visual search data reindex'));

        $arguments = new ArrayInput([
            'command' => 'indexer:reindex',
            'index' => [Indexer::INDEXER_ID]
        ]);

        $this->getApplication()->find('indexer:reindex')->run($arguments, $output);
    }
}
