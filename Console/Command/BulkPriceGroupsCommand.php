<?php

namespace RetailExpress\SkyLink\Console\Command;

use DateTimeImmutable;
use DateTimeZone;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroup as SkyLinkPriceGroup;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepositoryFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkPriceGroupsCommand extends Command
{
    private $skyLinkPriceGroupRepositoryFactory;

    private $commandBus;

    private $adminEmulator;

    public function __construct(
        PriceGroupRepositoryFactory $skyLinkPriceGroupRepositoryFactory,
        CommandBusInterface $commandBus,
        AdminEmulator $adminEmulator
    ) {
        $this->skyLinkPriceGroupRepositoryFactory = $skyLinkPriceGroupRepositoryFactory;
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:bulk-price-groups');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of Price Groups from Retail Express and queues a command for each one to sync to a Magento Customer Group')
            ->addOption('inline', null, InputOption::VALUE_NONE, 'Flag to sync inline rather than queue a command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        if (true === $shouldBeQueued) {
            $output->writeln('Fetching Price Groups from Retail Express...');
        } else {
            $output->writeln('Syncing Price Groups from Retail Express...');
        }

        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepository $skyLinkPriceGroupRepository */
        $skyLinkPriceGroupRepository = $this->skyLinkPriceGroupRepositoryFactory->create();

        /* @var SkyLinkPriceGroup[] $skyLinkPriceGroups */
        $skyLinkPriceGroups = $skyLinkPriceGroupRepository->all();

        if (0 === count($skyLinkPriceGroups)) {
            $output->writeln('<info>There are no Price Groups in Retail Express.</info>');
            return;
        }

        $progressBar = new ProgressBar($output, count($skyLinkPriceGroups));
        $progressBar->start();

        // Loop over our Price Groups and add dispatch a command to sync each
        array_walk(
            $skyLinkPriceGroups,
            function (SkyLinkPriceGroup $skyLinkPriceGroup) use ($shouldBeQueued, $progressBar) {

                $command = new SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand();
                $command->skyLinkPriceGroupKey = (string) $skyLinkPriceGroup->getKey();
                $command->shouldBeQueued = $shouldBeQueued;

                if (true === $shouldBeQueued) {
                    $this->commandBus->handle($command);
                } else {
                    $this->adminEmulator->onAdmin(function () use ($command) {
                        $this->commandBus->handle($command);
                    });
                }

                $progressBar->advance();
            }
        );

        $progressBar->finish();
        $output->writeln('');

        if (true === $shouldBeQueued) {
            $output->writeln(sprintf(<<<'MESSAGE'
<info>%s Retail Express Price Groups have had commands queued to sync them to Magento Customer Groups.
Ensure that an instance of 'retail-express:command-bus:consume-queue price-groups' is running to perform the actual sync.</info>
MESSAGE
                ,
                count($skyLinkPriceGroups)
            ));
        } else {
            $output->writeln(sprintf('<info>%s Price Groups have been synced to Magento Customer Groups.</info>', count($skyLinkPriceGroups)));
        }
    }

    /**
     * Determines if the command should be qeueud.
     *
     * @return bool
     */
    private function shouldBeQueued(InputInterface $input)
    {
        return !$input->getOption('inline');
    }
}
