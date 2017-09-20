<?php

namespace RetailExpress\SkyLink\Observer\Customers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\Entity;
use RetailExpress\SkyLink\Eds\EntityType;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroup as SkyLinkPriceGroup;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepositoryFactory;

class WhenEdsChangeSetWasRegisteredForCustomerGroups implements ObserverInterface
{
    private $skyLinkPriceGroupRepositoryFactory;

    private $magentoSimpleProductRepository;

    private $commandBus;

    public function __construct(
        PriceGroupRepositoryFactory $skyLinkPriceGroupRepositoryFactory,
        MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository,
        CommandBusInterface $commandBus
    ) {
        $this->skyLinkPriceGroupRepositoryFactory = $skyLinkPriceGroupRepositoryFactory;
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;
        $this->commandBus = $commandBus;
    }

    public function execute(Observer $observer)
    {
        $changeSet = $observer->getData('change_set');

        // Build commands
        $nestedCommands = array_filter(array_map(function (Entity $entity) use ($changeSet) {
            if ($entity->getType()->sameValueAs(EntityType::get('price_group'))) {

                // Firstly, we'll create a command to sync each of the price groups
                $commands = $this->createSyncSkyLinkPriceGroupToMagentoCustomerGroupCommands();

                // Then, we'll sync all existing mapped productcs
                $commands = array_merge(
                    $commands,
                    $this->createSyncSkyLinkProductToMagentoProductCommands()
                );

                return $commands;
            }
        }, $changeSet->getEntities()));

        $commands = array_flatten($nestedCommands);

        // Loop through and execute our commands
        array_map(function ($command) use ($changeSet) {
            $command->batchId = $changeSet->getId()->toNative();
            $this->commandBus->handle($command);
        }, $commands);
    }

    private function createSyncSkyLinkPriceGroupToMagentoCustomerGroupCommands()
    {
        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepository $skyLinkPriceGroupRepository */
        $skyLinkPriceGroupRepository = $this->skyLinkPriceGroupRepositoryFactory->create();

        return array_map(function (SkyLinkPriceGroup $skyLinkPriceGroup) {
            $command = new SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand();
            $command->skyLinkPriceGroupKey = (string) $skyLinkPriceGroup->getKey();

            return $command;
        }, $skyLinkPriceGroupRepository->all());
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
