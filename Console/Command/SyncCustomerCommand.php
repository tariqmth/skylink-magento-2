<?php

namespace RetailExpress\SkyLink\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;

class SyncCustomerCommand extends Command
{
    private $commandBus;

    private $adminEmulator;

    public function __construct(CommandBusInterface $commandBus, AdminEmulator $adminEmulator)
    {
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:sync-customer');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Syncs a customer from Retail Express')
            ->addArgument('skylink-customer-id', InputArgument::REQUIRED, 'The SkyLink Customer ID to sync')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Flag to queue a command rather than sync inline');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var SkyLinkCustomerId $skyLinkCustomerId */
        $skyLinkCustomerId = $this->getSkyLinkCustomerId($input);

        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        $command = new SyncSkyLinkCustomerToMagentoCustomerCommand();
        $command->skyLinkCustomerId = (string) $skyLinkCustomerId;
        $command->shouldBeQueued = $shouldBeQueued;

        if (true === $shouldBeQueued) {
            $output->writeln(sprintf('Queueing SkyLink Customer #%s to be synced...', $skyLinkCustomerId));
            $this->commandBus->handle($command);
            $output->writeln("<info>Ensure that an instance of 'retail-express:command-bus:consume-queue customers' is running to perform the actual sync.</info>");

            return;
        }

        $output->writeln(sprintf('Syncing SkyLink Customer #%s...', $skyLinkCustomerId));
        $this->adminEmulator->onAdmin(function () use ($command) {
            $this->commandBus->handle($command);
        });
        $output->writeln('<info>Done.</info>');
    }

    private function getSkyLinkCustomerId(InputInterface $input)
    {
        return new SkyLinkCustomerId($input->getArgument('skylink-customer-id'));
    }

    private function shouldBeQueued(InputInterface $input)
    {
        return $input->getOption('queue');
    }
}
