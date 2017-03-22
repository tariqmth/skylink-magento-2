<?php

namespace RetailExpress\SkyLink\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Sales\Shipments\SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

class SyncFulfillmentsCommand extends Command
{
    private $commandBus;

    private $adminEmulator;

    public function __construct(CommandBusInterface $commandBus, AdminEmulator $adminEmulator)
    {
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:sync-fulfillments');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Syncs new fulfillments for an order from Retail Express')
            ->addArgument('skylink-order-id', InputArgument::REQUIRED, 'The SkyLink Order ID to sync fulfillments for')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Flag to queue a job rather than sync inline');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var SkyLinkOrderId $skyLinkOrderId */
        $skyLinkOrderId = $this->getSkyLinkOrderId($input);

        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        $command = new SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand();
        $command->skyLinkOrderId = (string) $skyLinkOrderId;
        $command->shouldBeQueued = $shouldBeQueued;

        if (true === $shouldBeQueued) {
            $output->writeln(sprintf('Queueing SkyLink Order #%s to have it\'s fulfillments synced...', $skyLinkOrderId));
            $this->commandBus->handle($command);
            $output->writeln("<info>Ensure that an instance of 'retail-express:command-bus:consume-queue fulfillments' is running to perform the actual sync.</info>");

            return;
        }

        $output->writeln(sprintf('Syncing fulfillments for SkyLink Order #%s...', $skyLinkOrderId));
        $this->adminEmulator->onAdmin(function () use ($command) {
            $this->commandBus->handle($command);
        });
        $output->writeln('<info>Done.</info>');
    }

    private function getSkyLinkOrderId(InputInterface $input)
    {
        return new SkyLinkOrderId($input->getArgument('skylink-order-id'));
    }

    private function shouldBeQueued(InputInterface $input)
    {
        return $input->getOption('queue');
    }
}
