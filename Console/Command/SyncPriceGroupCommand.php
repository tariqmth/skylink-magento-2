<?php

namespace RetailExpress\SkyLink\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkPriceGroupToMagentoCustomerGroupHandler;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

class SyncPriceGroupCommand extends Command
{
    private $commandBus;

    private $adminEmulator;

    public function __construct(CommandBusInterface $commandBus, AdminEmulator $adminEmulator)
    {
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:sync-price-group');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Syncs a price group from Retail Express to a Magento Customer Group')
            ->addArgument('skylink-price-group-type', InputArgument::REQUIRED, 'The SkyLink Price Group Type (standard or fixed)')
            ->addArgument('skylink-price-group-id', InputArgument::REQUIRED, 'The SkyLink Price Group ID')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Flag to queue a job rather than sync inline');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var SkyLinkPriceGroupKey $skyLinkCustomerId */
        $skyLinkPriceGroupKey = $this->getSkyLinkPriceGroupKey($input);

        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        $command = new SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand();
        $command->skyLinkPriceGroupKey = (string) $skyLinkPriceGroupKey;
        $command->shouldBeQueued = $shouldBeQueued;

        if (true === $shouldBeQueued) {
            $output->writeln(sprintf(
                'Queueing SkyLink %s Price Group #%s to be synced...',
                $skyLinkPriceGroupKey->getType()->getPriceGroupTypeName(),
                $skyLinkPriceGroupKey->getId()
            ));
            $this->commandBus->handle($command);
            $output->writeln("<info>Ensure that an instance of 'retail-express:command-bus:consume-queue price-groups' is running to perform the actual sync.</info>");

            return;
        }

        $output->writeln(sprintf(
            'Syncing SkyLink %s Price Group #%s...',
            $skyLinkPriceGroupKey->getType()->getPriceGroupTypeName(),
            $skyLinkPriceGroupKey->getId()
        ));
        $this->adminEmulator->onAdmin(function () use ($command) {
            $this->commandBus->handle($command);
        });
        $output->writeln('<info>Done.</info>');
    }

    private function getSkyLinkPriceGroupKey(InputInterface $input)
    {
        return SkyLinkPriceGroupKey::fromNative(
            $input->getArgument('skylink-price-group-type'),
            $input->getArgument('skylink-price-group-id')
        );
    }

    private function shouldBeQueued(InputInterface $input)
    {
        return $input->getOption('queue');
    }
}
