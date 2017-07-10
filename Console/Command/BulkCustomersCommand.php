<?php

namespace RetailExpress\SkyLink\Console\Command;

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

    private $adminEmulator;

    public function __construct(
        CustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        CommandBusInterface $commandBus,
        AdminEmulator $adminEmulator
    ) {
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:bulk-customers');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of Customers from Retail Express and queues a command for each one to sync')
            ->addOption('inline', null, InputOption::VALUE_NONE, 'Flag to sync inline rather than queue a command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Customers\CustomerRepository $skyLinkCustomerRepository */
        $skyLinkCustomerRepository = $this->skyLinkCustomerRepositoryFactory->create();

        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        if (true === $shouldBeQueued) {
            $output->writeln('Fetching Customer IDs from Retail Express...');
        } else {
            $output->writeln('Syncing Customers from Retail Express...');
        }

        /* @var SkyLinkCustomerId[] $skyLinkCustomerIds */
        $skyLinkCustomerIds = $skyLinkCustomerRepository->allIds();

        $progressBar = new ProgressBar($output, count($skyLinkCustomerIds));
        $progressBar->start();

        if (0 === count($skyLinkCustomerIds)) {
            $output->writeln('<info>There are no Customers in Retail Express.</info>');
            return;
        }

        // Loop over our IDs and add dispatch a command to sync each
        array_walk(
            $skyLinkCustomerIds,
            function (SkyLinkCustomerId $skyLinkCustomerId) use ($shouldBeQueued, $progressBar) {
                $command = new SyncSkyLinkCustomerToMagentoCustomerCommand();
                $command->skyLinkCustomerId = (string) $skyLinkCustomerId;
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
<info>%s Customers have had commands queued to sync them.
Ensure that an instance of 'retail-express:command-bus:consume-queue customers' is running to perform the actual sync.</info>
MESSAGE
                ,
                count($skyLinkCustomerIds)
            ));
        } else {
            $output->writeln(sprintf('<info>%s Customers have been synced.</info>', count($skyLinkCustomerIds)));
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
