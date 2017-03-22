<?php

namespace RetailExpress\SkyLink\Observer\Customers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Observer\Eds\EntityProcessor;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupId as SkyLinkPriceGroupId;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Eds\EntityType as EdsEntityType;

class WhenSkyLinkPriceGroupWasSyncedToMagentoCustomerGroupOrProduct implements ObserverInterface
{
    use EntityProcessor;

    public function execute(Observer $observer)
    {
        /* @var \RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand|
                \RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand $command */
        $command = $observer->getData('command');

        // If we're not interested in the command, let's bail early
        if (null === $command->changeSetId || null === $command->skyLinkPriceGroupId) {
            return;
        }

        $changeSetId = new ChangeSetId($command->changeSetId);
        $skyLinkPriceGroupId = new SkyLinkPriceGroupId($command->skyLinkPriceGroupId);

        /* @var \RetailExpress\SkyLink\Eds\Entity|null $edsEntity */
        $edsEntity = $this->getMatchingEdsEntity(
            $changeSetId,
            EdsEntityType::get('price_group'),
            $skyLinkPriceGroupId
        );

        if (null === $edsEntity) {
            return;
        }

        $this->changeSetService->processEntity($edsEntity);
    }
}
