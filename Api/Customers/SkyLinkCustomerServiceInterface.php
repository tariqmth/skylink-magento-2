<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

interface SkyLinkCustomerServiceInterface
{
    /**
     * Due to how Retail Express expects customers to be idempotently persisted,
     * we have a single method to handle this and update the properties of the
     * Magento Customer.
     *
     * @param SkyLinkCustomer   $skyLinkCustomer
     * @param CustomerInterface $magentoCustomer
     */
    public function pushSkyLinkCustomer(SkyLinkCustomer $skyLinkCustomer, CustomerInterface $magentoCustomer);
}
