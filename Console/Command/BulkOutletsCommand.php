<?php

namespace RetailExpress\SkyLink\Console\Command;

use DateTimeImmutable;
use DateTimeZone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Commands\Outlets\SyncSkyLinkOutletCommand;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;
use RetailExpress\SkyLink\Sdk\Outlets\OutletRepositoryFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkOutletsCommand extends Command
{
    private $config;

    private $skyLinkOutletRepositoryFactory;

    private $commandBus;

    public function __construct(
        ConfigInterface $config,
        OutletRepositoryFactory $skyLinkOutletRepositoryFactory,
        CommandBusInterface $commandBus,
        TimezoneInterface $timezone
    ) {
        $this->config = $config;
        $this->skyLinkOutletRepositoryFactory = $skyLinkOutletRepositoryFactory;
        $this->commandBus = $commandBus;

        parent::__construct('retail-express:skylink:bulk-outlets');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of outlets from Retail Express and queues a job for each one to sync');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Outlets\OutletRepository $skyLinkOutletRepository */
        $skyLinkOutletRepository = $this->skyLinkOutletRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId $salesChannelId */
        $salesChannelId = $this->config->getSalesChannelId();

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /* @var SkyLinkOutlet[] $skyLinkOutlets */
        $skyLinkOutlets = $skyLinkOutletRepository->all($salesChannelId);

        // Loop over our IDs and add dispatch a command to sync each
        array_walk($skyLinkOutlets, function (SkyLinkOutlet $skyLinkOutlet) use ($progressBar) {
            $command = new SyncSkyLinkOutletCommand();
            $command->outletId = (string) $skyLinkOutlet->getId();

            $this->commandBus->handle($command);

            $progressBar->advance();
        });

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf(<<<MESSAGE
<info>%s outlets have had jobs queued to sync them.
Ensure that an instance of 'retail-express:command-bus:consume-queue outlets' is running to perform the actual sync.</info>
MESSAGE
            ,
            count($skyLinkOutlets)
        ));
    }

    /**
     * @return \RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId
     */
    private function getSalesChannelId()
    {
        return $this->config->getSalesChannelId();
    }
}
