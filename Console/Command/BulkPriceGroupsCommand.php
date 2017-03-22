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

    public function __construct(
        PriceGroupRepositoryFactory $skyLinkPriceGroupRepositoryFactory,
        CommandBusInterface $commandBus
    ) {
        $this->skyLinkPriceGroupRepositoryFactory = $skyLinkPriceGroupRepositoryFactory;
        $this->commandBus = $commandBus;

        parent::__construct('retail-express:skylink:bulk-price-groups');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of price groups from Retail Express and queues a job for each one to sync to a Magento customer group');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepository $skyLinkPriceGroupRepository */
        $skyLinkPriceGroupRepository = $this->skyLinkPriceGroupRepositoryFactory->create();

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /* @var SkyLinkPriceGroup[] $skyLinkPriceGroups */
        $skyLinkPriceGroups = $skyLinkPriceGroupRepository->all();

        // Loop over our Price Groups and add dispatch a command to sync each
        array_walk($skyLinkPriceGroups, function (SkyLinkPriceGroup $skyLinkPriceGroup) use ($progressBar) {

            $command = new SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand();
            $command->skyLinkPriceGroupKey = (string) $skyLinkPriceGroup->getKey();

            $this->commandBus->handle($command);

            $progressBar->advance();
        });

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf(<<<'MESSAGE'
<info>%s Retail Express price groups have had jobs queued to sync them to Magento customer groups.
Ensure that an instance of 'retail-express:command-bus:consume-queue customer_groups' is running to perform the actual sync.</info>
MESSAGE
            ,
            count($skyLinkPriceGroups)
        ));
    }
}
