<?php

namespace RetailExpress\SkyLink\Console\Command;

use DateTimeImmutable;
use DateTimeZone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepositoryFactory;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelGroupRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkProductsCommand extends Command
{
    private $config;

    private $skyLinkProductRepositoryFactory;

    private $commandBus;

    private $timezone;

    private $adminEmulator;

    public function __construct(
        ConfigInterface $config,
        ProductRepositoryFactory $skyLinkProductRepositoryFactory,
        CommandBusInterface $commandBus,
        TimezoneInterface $timezone,
        AdminEmulator $adminEmulator,
        SalesChannelGroupRepositoryInterface $salesChannelGroupRepository
    ) {
        $this->config = $config;
        $this->skyLinkProductRepositoryFactory = $skyLinkProductRepositoryFactory;
        $this->commandBus = $commandBus;
        $this->timezone = $timezone;
        $this->adminEmulator = $adminEmulator;
        $this->salesChannelGroupRepository = $salesChannelGroupRepository;

        parent::__construct('retail-express:skylink:bulk-products');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of products from Retail Express and queues a command for each one to sync')
            ->addOption('since', null, InputOption::VALUE_REQUIRED, 'Only products updated in Retail Express within the specified timeframe (in seconds) will be synced.')
            ->addOption('inline', null, InputOption::VALUE_NONE, 'Flag to sync inline rather than queue a command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepository $skyLinkProductRepository */
        $skyLinkProductRepository = $this->skyLinkProductRepositoryFactory->create();

        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        /* @var DateTimeImmutable|null $sinceDate */
        $sinceDate = $this->getSinceDate($input);

        if (true === $shouldBeQueued) {
            $output->writeln('Fetching Product IDs from Retail Express...');
        } else {
            $output->writeln('Syncing Products from Retail Express...');
        }

        $skyLinkProductIds = [];

        foreach ($this->salesChannelGroupRepository->getList() as $salesChannel) {
            $salesChannelId = $salesChannel->getSalesChannelId();
            $productsForChannel = $skyLinkProductRepository->allIds($salesChannelId, $sinceDate);
            $skyLinkProductIds = array_merge($skyLinkProductIds, $productsForChannel);
            $skyLinkProductIds = array_unique($skyLinkProductIds);
        }

        if (0 === count($skyLinkProductIds)) {
            $output->writeln('<info>There are no Products in Retail Express.</info>');
            return;
        }

        $progressBar = new ProgressBar($output, count($skyLinkProductIds));
        $progressBar->start();

        $batchId = str_random();

        // Loop over our IDs and add dispatch a command to sync each
        array_walk(
            $skyLinkProductIds,
            function (SkyLinkProductId $skyLinkProductId) use ($shouldBeQueued, $progressBar, $batchId) {

                // Create a new command to sync the product
                $command = new SyncSkyLinkProductToMagentoProductCommand();
                $command->skyLinkProductId = (string) $skyLinkProductId;
                $command->potentialCompositeProductRerun = true;
                $command->shouldBeQueued = $shouldBeQueued;
                $command->batchId = $batchId;

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
<info>%s Products have had commands queued to sync them.
Ensure that an instance of 'retail-express:command-bus:consume-queue products' is running to perform the actual sync.</info>
MESSAGE
                ,
                count($skyLinkProductIds)
            ));
        } else {
            $output->writeln(sprintf('<info>%s Products have been synced.</info>', count($skyLinkProductIds)));
        }
    }

    /**
     * @return DateTimeImmutable|null
     */
    private function getSinceDate(InputInterface $input)
    {
        $sinceSeconds = $input->getOption('since');

        if (null === $sinceSeconds) {
            return null;
        }

        // Retail Express doesn't deal with UTC time, so we'll use Magento's current store
        // time instead, assuming that matches Retail Express' configured timezone.
        $timezone = new DatetimeZone($this->timezone->getConfigTimezone());
        $nowDate = new DateTimeImmutable('now', $timezone);

        return $nowDate->modify(sprintf('-%d seconds', $sinceSeconds));
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
