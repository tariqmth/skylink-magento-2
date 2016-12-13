<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerCommand;

trait CustomerSyncer
{
    /**
     * @var \RetailExpress\CommandBus\Api\CommandBusInterface
     */
    private $commandBus;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Syncs the given Magento Customer back to Retail Express, checking to see if there's any syncing locks in place.
     *
     * @param CustomerInterface $magentoCustomer
     */
    public function sync(CustomerInterface $magentoCustomer)
    {
        // If the registry is locked by syncing a SkyLink Customer to a Magento Customer, we're
        // not going to trigger a reverse sync as a result of a customer or address being saved
        if ($this->registry->registry(MagentoCustomerServiceInterface::REGISTRY_LOCK_KEY)) {
            return;
        }

        $command = new SyncMagentoCustomerToSkyLinkCustomerCommand();
        $command->magentoCustomerId = $magentoCustomer->getId();

        $this->commandBus->handle($command);
    }
}
