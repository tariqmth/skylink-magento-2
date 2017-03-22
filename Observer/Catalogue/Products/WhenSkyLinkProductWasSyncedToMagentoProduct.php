<?php

namespace RetailExpress\SkyLink\Observer\Catalogue\Products;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Eds\EntityType as EdsEntityType;
use RetailExpress\SkyLink\Observer\Eds\EntityProcessor;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

// @todo remove repitition of these commands? (i.e. when skylink customer synced to magento customer)
class WhenSkyLinkProductWasSyncedToMagentoProduct implements ObserverInterface
{
    use EntityProcessor;

    public function execute(Observer $observer)
    {
        /* @var \RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand $command */
        $command = $observer->getData('command');

        if (null === $command->changeSetId) {
            return;
        }

        $changeSetId = new ChangeSetId($command->changeSetId);
        $skyLinkProductId = new SkyLinkProductId($command->skyLinkProductId);

        /* @var \RetailExpress\SkyLink\Eds\Entity|null $edsEntity */
        $edsEntity = $this->getMatchingEdsEntity(
            $changeSetId,
            EdsEntityType::get('product'),
            $skyLinkProductId
        );

        if (null === $edsEntity) {
            return;
        }

        $this->changeSetService->processEntity($edsEntity);
    }
}
