<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

interface MagentoCustomerServiceInterface
{
    const REGISTRY_LOCK_KEY = 'skylink_customer_to_magento_customer_lock';

    /**
     * Register a new Magento Customer from the given SkyLink Customer.
     *
     * @param SkyLinkCustomer $skyLinkCustomer
     *
     * @return CustomerInterface
     */
    public function registerMagentoCustomer(SkyLinkCustomer $skyLinkCustomer);

    /**
     * Updates the given Magento Customer with the information from the SkyLink Customer.
     *
     * @param CustomerInterface $magentoCustomer
     * @param SkyLinkCustomer   $skyLinkCustomer
     */
    public function updateMagentoCustomer(CustomerInterface $magentoCustomer, SkyLinkCustomer $skyLinkCustomer);
}
