<?php

namespace RetailExpress\SkyLink\Observer\Customers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand;
use RetailExpress\SkyLink\Eds\Entity;
use RetailExpress\SkyLink\Eds\EntityType;

class WhenEdsChangeSetWasRegistered implements ObserverInterface
{
    private $commandBus;

    private $config;

    public function __construct(
        CommandBusInterface $commandBus,
        ConfigInterface $config
    ) {
        $this->commandBus = $commandBus;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        $changeSet = $observer->getData('change_set');

        // Build commands
        $commands = array_filter(array_map(function (Entity $entity) {

            if ($entity->getType()->sameValueAs(EntityType::get('customer'))) {
                return $this->createSyncSkyLinkCustomerToMagentoCustomerCommand($entity);
            }

            if ($entity->getType()->sameValueAs(EntityType::get('product'))) {
                return $this->createSyncSkyLinkProductToMagentoProductCommand($entity);
            }

        }, $changeSet->getEntities()));

        // Loop through and execute our commands
        array_map(function ($command) use ($changeSet) {
            $command->changeSetId = $changeSet->getId()->toNative();
            $this->commandBus->handle($command);
        }, $commands);
    }

    private function createSyncSkyLinkCustomerToMagentoCustomerCommand(Entity $entity)
    {
        $command = new SyncSkyLinkCustomerToMagentoCustomerCommand();
        $command->skyLinkCustomerId = $entity->getId()->toNative();
        return $command;
    }

    private function createSyncSkyLinkProductToMagentoProductCommand(Entity $entity)
    {
        $command = new SyncSkyLinkProductToMagentoProductCommand();
        $command->skyLinkProductId = $entity->getId()->toNative();
        $command->salesChannelId = $this->config->getSalesChannelId()->toNative();
        return $command;
    }
}
