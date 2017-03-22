<?php

namespace RetailExpress\SkyLink\Observer\Customers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Observer\Eds\EntityProcessor;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Eds\EntityType as EdsEntityType;

class WhenSkyLinkCustomerWasSyncedToMagentoCustomer implements ObserverInterface
{
    use EntityProcessor;

    public function execute(Observer $observer)
    {
        /* @var \RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand $command */
        $command = $observer->getData('command');

        if (null === $command->changeSetId) {
            return;
        }

        $changeSetId = new ChangeSetId($command->changeSetId);
        $skyLinkCustomerId = new SkyLinkCustomerId($command->skyLinkCustomerId);

        /* @var \RetailExpress\SkyLink\Eds\Entity|null $edsEntity */
        $edsEntity = $this->getMatchingEdsEntity(
            $changeSetId,
            EdsEntityType::get('customer'),
            $skyLinkCustomerId
        );

        if (null === $edsEntity) {
            return;
        }

        $this->changeSetService->processEntity($edsEntity);
    }
}
