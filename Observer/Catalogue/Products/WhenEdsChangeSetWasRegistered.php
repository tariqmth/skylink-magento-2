<?php

namespace RetailExpress\SkyLink\Observer\Catalogue\Products;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand;
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
        $commands = array_filter(array_map(function (Entity $entity) {
            if ($entity->getType()->sameValueAs(EntityType::get('product'))) {
                return $this->createSyncSkyLinkProductToMagentoProductCommand($entity);
            }
        }, $changeSet->getEntities()));

        $isBulk = count($commands) > 1;

        // Loop through and execute our commands
        array_map(function ($command) use ($changeSet, $isBulk) {
            if (true === $isBulk) {
                $command->potentialCompositeProductRerun = true;
            }

            $command->changeSetId = $changeSet->getId()->toNative();
            $this->commandBus->handle($command);
        }, $commands);
    }

    private function createSyncSkyLinkProductToMagentoProductCommand(Entity $entity)
    {
        $command = new SyncSkyLinkProductToMagentoProductCommand();
        $command->skyLinkProductId = $entity->getId()->toNative();

        return $command;
    }
}
