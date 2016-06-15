<?php

namespace RetailExpress\SkyLinkMagento2\Console\Command;

use League\Tactician\CommandBus;
use Magento\Framework\ObjectManagerInterface;
use RetailExpress\SkyLinkMagento2\Commands\SyncCustomerCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WipCommand extends Command
{
    private $objectManager;

    private $commandBus;

    public function __construct(ObjectManagerInterface $objectManager, CommandBus $commandBus)
    {
        $this->objectManager = $objectManager;
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('skylink:wip')
            ->setDescription('Command to hold all WIP code for SkyLink');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->objectManager->create(SyncCustomerCommand::class, [
            'retailExpressCustomerId' => 124001,
        ]);

        $response = $this->commandBus->handle($command);

        $output->writeln("<info>{$response}</info>");
    }
}

