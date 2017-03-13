<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Customers;

use Magento\Customer\Model\CustomerRegistry;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerHandler;

/**
 * This class exists because the Customer Registry is not updating references to Customers after they're
 * saved, meaning that in a queue worker, when a customer is retrieved, we're retrieving the data
 * from the Customer at the time when the worker starts. We're only overriding it here because
 * it's obviously a caching attempt by Magento, so we'll only bust the cache when it's
 * absolutely necessary.
 */
class SyncMagentoCustomerToSkyLinkCustomerCacheBusterPlugin
{
    private $magentoCustomerRegistry;

    public function __construct(CustomerRegistry $magentoCustomerRegistry)
    {
        $this->magentoCustomerRegistry = $magentoCustomerRegistry;
    }

    public function beforeHandle(
        SyncMagentoCustomerToSkyLinkCustomerHandler $subject,
        SyncMagentoCustomerToSkyLinkCustomerCommand $command
    ) {
        $this->magentoCustomerRegistry->remove($command->magentoCustomerId);

        return [$command];
    }
}
