<?php

namespace RetailExpress\SkyLink\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class SyncProductCommand extends Command
{
    private $commandBus;

    private $adminEmulator;

    public function __construct(CommandBusInterface $commandBus, AdminEmulator $adminEmulator)
    {
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:sync-product');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Syncs a product from Retail Express')
            ->addArgument('skylink-product-id', InputArgument::REQUIRED, 'The SkyLink Product ID to sync')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Flag to queue a job rather than sync inline');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var SkyLinkProductId $skyLinkProductId */
        $skyLinkProductId = $this->getSkyLinkProductId($input);

        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        $command = new SyncSkyLinkProductToMagentoProductCommand();
        $command->skyLinkProductId = (string) $skyLinkProductId;
        $command->shouldBeQueued = $shouldBeQueued;

        if (true === $shouldBeQueued) {
            $output->writeln(sprintf('Queueing SkyLink Product #%s to be synced...', $skyLinkProductId));
            $this->commandBus->handle($command);
            $output->writeln("<info>Ensure that an instance of 'retail-express:command-bus:consume-queue products' is running to perform the actual sync.</info>");

            return;
        }

        $output->writeln(sprintf('Syncing SkyLink Product #%s...', $skyLinkProductId));
        $this->adminEmulator->onAdmin(function () use ($command) {
            $this->commandBus->handle($command);
        });
        $output->writeln('<info>Done.</info>');
    }

    private function getSkyLinkProductId(InputInterface $input)
    {
        return new SkyLinkProductId($input->getArgument('skylink-product-id'));
    }

    private function shouldBeQueued(InputInterface $input)
    {
        return $input->getOption('queue');
    }
}
