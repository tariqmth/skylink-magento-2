<?php

namespace RetailExpress\SkyLink\Observer\Customers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand;
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

        array_map(function (Entity $entity) use ($changeSet) {
            if (!$entity->getType()->sameValueAs(EntityType::get('customer'))) {
                continue;
            }

            $command = new SyncSkyLinkCustomerToMagentoCustomerCommand();
            $command->skyLinkCustomerId = $entity->getId()->toNative();
            $command->changeSetId = $changeSet->getId()->toNative();

            $this->commandBus->handle($command);

        }, $changeSet->getEntities());
    }
}
