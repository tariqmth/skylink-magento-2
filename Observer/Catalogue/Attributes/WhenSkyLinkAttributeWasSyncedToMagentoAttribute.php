<?php

namespace RetailExpress\SkyLink\Observer\Catalogue\Attributes;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Eds\EntityType as EdsEntityType;
use RetailExpress\SkyLink\Observer\Eds\EntityProcessor;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOptionId as SkyLinkAttributeOptionId;

// @todo remove repitition of these commands? (i.e. when skylink customer synced to magento customer)
class WhenSkyLinkAttributeWasSyncedToMagentoAttribute implements ObserverInterface
{
    use EntityProcessor;

    public function execute(Observer $observer)
    {
        /* @var \RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkAttributeToMagentoAttributeCommand $command */
        $command = $observer->getData('command');

        if (null === $command->changeSetId) {
            return;
        }

        if (null === $command->skyLinkAttributeOptionId) {
            return;
        }

        $changeSetId = new ChangeSetId($command->changeSetId);
        $skyLinkAttributeOptionId = new SkyLinkAttributeOptionId($command->skyLinkAttributeOptionId);

        /* @var \RetailExpress\SkyLink\Eds\Entity|null $edsEntity */
        $edsEntity = $this->getMatchingEdsEntity(
            $changeSetId,
            EdsEntityType::get('attribute_option'),
            $skyLinkAttributeOptionId
        );

        if (null === $edsEntity) {
            return;
        }

        $this->changeSetService->processEntity($edsEntity);
    }
}
