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
use Magento\Framework\Indexer\IndexerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateVectorDataCommand
 * Update visual search data
 * @package BelSmol\VisualSearch\Console\Command
 */
class UpdateVectorDataCommand extends Command
{
    private const COMMAND_NAME = "visual-search:vector-data:update";
    private const COMMAND_DESCRIPTION = "Update visual search data. High-load operation.";

    private const SKUS_OPTION_NAME = 'skus';

    /**
     * @param State $state
     * @param ConfigStorageInterface $configStorage
     * @param VSDataManagerInterface $vsDataManager
     * @param IndexerRegistry $indexerRegistry
     * @param string|null $name
     */
    public function __construct(
        private State $state,
        private ConfigStorageInterface $configStorage,
        private VSDataManagerInterface $vsDataManager,
        private IndexerRegistry $indexerRegistry,
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

        $this->addOption(
            self::SKUS_OPTION_NAME,
            null,
            InputOption::VALUE_OPTIONAL,
            'Product skus separated by comma without spaces'
        );

        parent::configure();
    }

    /**
     * If empty 'skus' parameter then all data will be considered.
     *
     * example:
     * bin/magento visual-search:vector-data:update --skus XXX,YYY,ZZZ
     *
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

        $skus = $input->getOption(self::SKUS_OPTION_NAME)
            ? explode(',', $input->getOption(self::SKUS_OPTION_NAME))
            : [];

        $output->writeln(sprintf('<info>%s</info>', 'Image vector update start'));

        try {
            $updatedRowIds = $this->vsDataManager->updateVisualSearchData($skus);

            if ($updatedRowIds) {
                $output->writeln(sprintf('<info>%s</info>', 'Rows reindex start'));
                $this->reindex($updatedRowIds);
                $output->writeln(sprintf('<info>%s</info>', 'Rows reindex finished'));

            }
        } catch (Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return self::FAILURE;
        }

        $output->writeln(sprintf('<info>%s</info>', 'Image vector update has been finished'));

        return self::SUCCESS;
    }

    /**
     * @param array $rowIds
     * @return void
     */
    private function reindex(array $rowIds): void
    {
        $vsDataIndexer = $this->indexerRegistry->get(Indexer::INDEXER_ID);

        if (!$vsDataIndexer->isScheduled()) {
            $vsDataIndexer->reindexList($rowIds);
        }
    }
}
