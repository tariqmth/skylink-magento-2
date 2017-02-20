<?php

namespace RetailExpress\SkyLink\Console\Command;

use DateTimeImmutable;
use DateTimeZone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkCustomersCommand extends Command
{
    private $skyLinkCustomerRepositoryFactory;

    private $commandBus;

    private $timezone;

    public function __construct(
        CustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        CommandBusInterface $commandBus,
        TimezoneInterface $timezone
    ) {
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->commandBus = $commandBus;
        $this->timezone = $timezone;

        parent::__construct('retail-express:skylink:bulk-customers');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of customers from Retail Express and queues a job for each one to sync')
            ->addOption('since', null, InputOption::VALUE_REQUIRED, 'Only customers updated in Retail Express within the specified timeframe (in seconds) will be synced.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Customers\CustomerRepository $skyLinkCustomerRepository */
        $skyLinkCustomerRepository = $this->skyLinkCustomerRepositoryFactory->create();

        /* @var DateTimeImmutable|null $sinceDate */
        $sinceDate = $this->getSinceDate($input);

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /* @var SkyLinkCustomerId[] $skyLinkCustomerIds */
        $skyLinkCustomerIds = $skyLinkCustomerRepository->allIds($sinceDate);

        // Loop over our IDs and add dispatch a command to sync each
        array_walk($skyLinkCustomerIds, function (SkyLinkCustomerId $skyLinkCustomerId) use ($progressBar) {
            $command = new SyncSkyLinkCustomerToMagentoCustomerCommand();
            $command->skyLinkCustomerId = (string) $skyLinkCustomerId;

            $this->commandBus->handle($command);

            $progressBar->advance();
        });

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf(<<<'MESSAGE'
<info>%s customers have had jobs queued to sync them.
Ensure that an instance of 'retail-express:command-bus:consume-queue customers' is running to perform the actual sync.</info>
MESSAGE
            ,
            count($skyLinkCustomerIds)
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

        // @see BulkProductCommand
        $timezone = new DateTimeZone($this->timezone->getConfigTimezone());
        $nowDate = new DateTimeImmutable('now', $timezone);

        return $nowDate->modify(sprintf('-%d seconds', $sinceSeconds));
    }
}
