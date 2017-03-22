<?php

namespace RetailExpress\SkyLink\Observer\Customers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand;
use RetailExpress\SkyLink\Eds\Entity;
use RetailExpress\SkyLink\Eds\EntityType;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

class WhenEdsChangeSetWasRegisteredForCustomerGroups implements ObserverInterface
{
    private $magentoCustomerGroupRepository;

    private $magentoSimpleProductRepository;

    private $commandBus;

    public function __construct(
        MagentoCustomerGroupRepositoryInterface $magentoCustomerGroupRepository,
        MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository,
        CommandBusInterface $commandBus
    ) {
        $this->magentoCustomerGroupRepository = $magentoCustomerGroupRepository;
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;
        $this->commandBus = $commandBus;
    }

    public function execute(Observer $observer)
    {
        $changeSet = $observer->getData('change_set');

        // Build commands
        $nestedCommands = array_filter(array_map(function (Entity $entity) {
            if ($entity->getType()->sameValueAs(EntityType::get('price_group'))) {

                // Firstly, we'll create a command to sync each of the price groups
                $commands = $this->createSyncSkyLinkPriceGroupToMagentoCustomerGroupCommands();

                // Then, we'll sync all existing mapped productcs
                $commands = array_merge(
                    $commands,
                    $this->createSyncSkyLinkProductToMagentoProductCommands()
                );

                // We'll add the price group ID
                // @see \RetailExpress\SkyLink\Observer\Catalogue\Attributes\WhenEdsChangeSetWasRegistered
                $lastCommand = end($commands);
                $lastCommand->skyLinkPriceGroupId = $entity->getId()->toNative();

                return $commands;
            }
        }, $changeSet->getEntities()));

        $commands = array_flatten($nestedCommands);

        // Loop through and execute our commands
        array_map(function ($command) use ($changeSet) {
            $command->changeSetId = $changeSet->getId()->toNative();
            $this->commandBus->handle($command);
        }, $commands);
    }

    private function createSyncSkyLinkPriceGroupToMagentoCustomerGroupCommands()
    {
        return array_map(function (SkyLinkPriceGroupKey $skyLinkPriceGroupKey) {
            $command = new SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand();
            $command->skyLinkPriceGroupKey = (string) $skyLinkPriceGroupKey;

            return $command;
        }, $this->magentoCustomerGroupRepository->getListOfMappedPriceGroupKeys());
    }

    private function createSyncSkyLinkProductToMagentoProductCommands()
    {
        return array_map(function (SkyLinkProductId $skyLinkProductId) {
            $command = new SyncSkyLinkProductToMagentoProductCommand();
            $command->skyLinkProductId = $skyLinkProductId->toNative();
            $command->potentialCompositeProductRerun = true; // We are syncing all products

            return $command;
        }, $this->magentoSimpleProductRepository->getListOfMappedSkyLinkProductIds());
    }
}
