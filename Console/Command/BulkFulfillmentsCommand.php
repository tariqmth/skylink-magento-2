<?php

namespace RetailExpress\SkyLink\Console\Command;

use DateTimeImmutable;
use DateTimeZone;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderRepositoryInterface;
use RetailExpress\SkyLink\Commands\Sales\Shipments\SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand;
use RetailExpress\SkyLink\Model\Sales\Orders\OrderExtensionAttributes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkFulfillmentsCommand extends Command
{
    use OrderExtensionAttributes;

    private $magentoOrderRepository;

    private $commandBus;

    private $adminEmulator;

    public function __construct(
        MagentoOrderRepositoryInterface $magentoOrderRepository,
        OrderExtensionFactory $orderExtensionFactory,
        CommandBusInterface $commandBus,
        AdminEmulator $adminEmulator
    ) {
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:bulk-fulfillments');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of active Magento Orders that are associated with SkyLink Orders and queues a command to sync their fulfillments');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /* @var OrderInterface[] $magentoOrders */
        $magentoOrders = $this->adminEmulator->onAdmin(function () {
            return $this->magentoOrderRepository->getListOfActiveWithSkyLinkOrderIds();
        });

        // Loop over our Price Groups and add dispatch a command to sync each
        array_walk($magentoOrders, function (OrderInterface $magentoOrder) use ($progressBar) {

            /* @var \Magento\Sales\Api\Data\OrderExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->getOrderExtensionAttributes($magentoOrder);

            $command = new SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand();
            $command->skyLinkOrderId = (string) $extendedAttributes->getSkyLinkOrderId();

            $this->commandBus->handle($command);

            $progressBar->advance();
        });

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf(<<<'MESSAGE'
<info>%s Retail Express Orders have had commands queued to sync their Fulfillments to Magento Shipments.
Ensure that an instance of 'retail-express:command-bus:consume-queue fulfillments' is running to perform the actual sync.</info>
MESSAGE
            ,
            count($magentoOrders)
        ));
    }
}
