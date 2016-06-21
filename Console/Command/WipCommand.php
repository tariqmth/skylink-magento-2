<?php

namespace RetailExpress\SkyLink\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use RetailExpress\CommandBus\CommandBus;
use RetailExpress\SkyLink\Commands\SyncCustomerCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WipCommand extends Command
{
    private $objectManager;

    private $commandBus;

    private $driver;

    public function __construct(ObjectManagerInterface $objectManager, CommandBus $commandBus, \RetailExpress\CommandBus\Queues\Drivers\MagentoDriver $driver)
    {
        $this->objectManager = $objectManager;
        $this->commandBus = $commandBus;
        $this->driver = $driver;
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
        // var_dump(['listQueues()' => $this->driver->listQueues()]);
        // $this->driver->createQueue('foo');
        // var_dump(['countMessages()' => $this->driver->countMessages('foo')]);
        // $this->driver->pushMessage('foo', json_encode(['time' => time()]));
        // var_dump($message = $this->driver->popMessage('foo'));
        // sleep(5);
        // $this->driver->acknowledgeMessage('foo', $message[1]);
        // var_dump(['peekQueue()' => $this->driver->peekQueue('foo')]);
        // $this->driver->removeQueue('foo');

        $command = $this->objectManager->create(SyncCustomerCommand::class, [
            'retailExpressCustomerId' => 124001,
        ]);

        $response = $this->commandBus->handle($command);

        $output->writeln("<info>{$response}</info>");
    }
}

