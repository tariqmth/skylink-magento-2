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

    public function __construct(
        ConfigInterface $config,
        ProductRepositoryFactory $skyLinkProductRepositoryFactory,
        CommandBusInterface $commandBus,
        TimezoneInterface $timezone
    ) {
        $this->config = $config;
        $this->skyLinkProductRepositoryFactory = $skyLinkProductRepositoryFactory;
        $this->commandBus = $commandBus;
        $this->timezone = $timezone;

        parent::__construct('retail-express:skylink:bulk-products');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of products from Retail Express and queues a job for each one to sync')
            ->addOption('since', null, InputOption::VALUE_REQUIRED, 'Only products updated in Retail Express within the specified timeframe (in seconds) will be synced.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepository $skyLinkProductRepository */
        $skyLinkProductRepository = $this->skyLinkProductRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId $salesChannelId */
        $salesChannelId = $this->config->getSalesChannelId();

        /* @var DateTimeImmutable|null $sinceDate */
        $sinceDate = $this->getSinceDate($input);

        $output->writeln('Fetching Product IDs from Retail Express...');

        /* @var SkyLinkProductId[] $skyLinkProductIds */
        $skyLinkProductIds = $skyLinkProductRepository->allIds($salesChannelId, $sinceDate);

        $progressBar = new ProgressBar($output, count($skyLinkProductIds));
        $progressBar->start();

        // Loop over our IDs and add dispatch a command to sync each
        array_walk(
            $skyLinkProductIds,
            function (SkyLinkProductId $skyLinkProductId) use ($progressBar) {

                // Create a new command to sync the product
                $command = new SyncSkyLinkProductToMagentoProductCommand();
                $command->skyLinkProductId = (string) $skyLinkProductId;
                $command->potentialCompositeProductRerun = true;

                $this->commandBus->handle($command);

                $progressBar->advance();
            }
        );

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf(<<<'MESSAGE'
<info>%s products have had jobs queued to sync them.
Ensure that an instance of 'retail-express:command-bus:consume-queue products' is running to perform the actual sync.</info>
MESSAGE
            ,
            count($skyLinkProductIds)
        ));
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
}
