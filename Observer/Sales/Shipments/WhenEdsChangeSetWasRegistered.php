<?php

namespace RetailExpress\SkyLink\Observer\Sales\Shipments;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Sales\Shipments\SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand;
use RetailExpress\SkyLink\Commands\Sales\Payments\SyncSkyLinkPaymentToMagentoPaymentCommand;
use RetailExpress\SkyLink\Eds\Entity;
use RetailExpress\SkyLink\Eds\EntityType;

class WhenEdsChangeSetWasRegistered implements ObserverInterface
{
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(Observer $observer)
    {
        $changeSet = $observer->getData('change_set');

        // Build commands
        $commands = array();
        foreach ($changeSet->getEntities() as $entity) {
            if (!$entity->getType()->sameValueAs(EntityType::get('order'))) {
                continue;
            }
            $shipmentCommand = new SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand();
            $shipmentCommand->skyLinkOrderId = (string) $entity->getId();
            $commands[] = $shipmentCommand;
            $paymentCommand = new SyncSkyLinkPaymentToMagentoPaymentCommand();
            $paymentCommand->skyLinkOrderId = (string) $entity->getId();
            $commands[] = $paymentCommand;
        }

        // Loop through and execute our commands
        array_map(function ($command) use ($changeSet) {
            $command->batchId = $changeSet->getId()->toNative();
            $this->commandBus->handle($command);
        }, $commands);
    }
}
