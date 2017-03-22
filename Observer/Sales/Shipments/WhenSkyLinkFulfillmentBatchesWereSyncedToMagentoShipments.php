<?php

namespace RetailExpress\SkyLink\Observer\Sales\Shipments;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Eds\EntityType as EdsEntityType;
use RetailExpress\SkyLink\Observer\Eds\EntityProcessor;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

class WhenSkyLinkFulfillmentBatchesWereSyncedToMagentoShipments implements ObserverInterface
{
    use EntityProcessor;

    public function execute(Observer $observer)
    {
        /* @var \RetailExpress\SkyLink\Commands\Sales\Shipments\SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand $command */
        $command = $observer->getData('command');

        if (null === $command->changeSetId) {
            return;
        }

        $changeSetId = new ChangeSetId($command->changeSetId);
        $skyLinkOrderId = new SkyLinkOrderId($command->skyLinkOrderId);

        /* @var \RetailExpress\SkyLink\Eds\Entity|null $edsEntity */
        $edsEntity = $this->getMatchingEdsEntity(
            $changeSetId,
            EdsEntityType::get('order'),
            $skyLinkOrderId
        );

        if (null === $edsEntity) {
            return;
        }

        $this->changeSetService->processEntity($edsEntity);
    }
}
