<?php

namespace RetailExpress\SkyLink\Observer\Catalogue\Attributes;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkAttributeCodeRepositoryInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Attributes\SyncSkyLinkAttributeToMagentoAttributeCommand;
use RetailExpress\SkyLink\Eds\Entity;
use RetailExpress\SkyLink\Eds\EntityType;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class WhenEdsChangeSetWasRegistered implements ObserverInterface
{
    private $commandBus;

    private $skyLinkAttributeCodeRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        SkyLinkAttributeCodeRepositoryInterface $skyLinkAttributeCodeRepository
    ) {
        $this->commandBus = $commandBus;
        $this->skyLinkAttributeCodeRepository = $skyLinkAttributeCodeRepository;
    }

    public function execute(Observer $observer)
    {
        $changeSet = $observer->getData('change_set');

        // Build commands
        $nestedCommands = array_filter(array_map(function (Entity $entity) {
            if ($entity->getType()->sameValueAs(EntityType::get('attribute_option'))) {
                return $this->createSyncSkyLinkAttributeToMagentoAttributeCommands($entity);
            }
        }, $changeSet->getEntities()));

        $commands = array_flatten($nestedCommands);

        // Loop through and execute our commands
        array_map(function ($command) use ($changeSet) {
            $command->batchId = $changeSet->getId()->toNative();
            $this->commandBus->handle($command);
        }, $commands);
    }

    private function createSyncSkyLinkAttributeToMagentoAttributeCommands(Entity $entity)
    {
        return array_map(function (SkyLinkAttributeCode $skyLinkAttributeCode) {
            $command = new SyncSkyLinkAttributeToMagentoAttributeCommand();
            $command->skyLinkAttributeCode = $skyLinkAttributeCode->toNative();

            return $command;
        }, $this->skyLinkAttributeCodeRepository->getList());
    }
}
