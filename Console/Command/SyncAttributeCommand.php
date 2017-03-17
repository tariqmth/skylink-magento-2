<?php

namespace RetailExpress\SkyLink\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Attributes\SyncSkyLinkAttributeToMagentoAttributeCommand;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class SyncAttributeCommand extends Command
{
    private $commandBus;

    private $adminEmulator;

    public function __construct(CommandBusInterface $commandBus, AdminEmulator $adminEmulator)
    {
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:sync-attribute');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Syncs an attribute from Retail Express')
            ->addArgument('skylink-attribute-code', InputArgument::REQUIRED, 'The SkyLink attribute code to sync from')
            ->addArgument('magento-attribute-code', InputArgument::OPTIONAL, 'The Magento attribute code to sync to (leave empty to use existing, already mapped attribute code)')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Flag to queue a job rather than sync inline');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var SkyLinkAttributeCode $skyLinkAttributeCode */
        $skyLinkAttributeCode = $this->getSkyLinkAttributeCode($input);

        /* @var string|null $magentoAttributeCode */
        $magentoAttributeCode = $this->getMagentoAttributeCode($input);

        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        $command = new SyncSkyLinkAttributeToMagentoAttributeCommand();
        $command->skyLinkAttributeCode = (string) $skyLinkAttributeCode;

        if (null !== $magentoAttributeCode) {
            $command->magentoAttributeCode = $magentoAttributeCode;
        }

        $command->shouldBeQueued = $shouldBeQueued;

        if (true === $shouldBeQueued) {
            $output->writeln(sprintf('Queueing SkyLink Attribute #%s to be synced...', $skyLinkAttributeCode));
            $this->commandBus->handle($command);
            $output->writeln("<info>Ensure that an instance of 'retail-express:command-bus:consume-queue attributes' is running to perform the actual sync.</info>");

            return;
        }

        $output->writeln(sprintf('Syncing SkyLink Attribute #%s...', $skyLinkAttributeCode));
        $this->adminEmulator->onAdmin(function () use ($command) {
            $this->commandBus->handle($command);
        });
        $output->writeln('<info>Done.</info>');
    }

    private function getSkyLinkAttributeCode(InputInterface $input)
    {
        return SkyLinkAttributeCode::get($input->getArgument('skylink-attribute-code'));
    }

    private function getMagentoAttributeCode(InputInterface $input)
    {
        return $input->getArgument('magento-attribute-code');
    }

    private function shouldBeQueued(InputInterface $input)
    {
        return $input->getOption('queue');
    }
}
