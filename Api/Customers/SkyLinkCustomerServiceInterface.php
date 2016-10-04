<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;

interface SkyLinkCustomerServiceInterface
{
    /**
     * Register a new SkyLink Customer from the given SkyLink Customer.
     *
     * @param CustomerInterface $magentoCustomer
     */
    public function registerSkyLinkCustomer(CustomerInterface $magentoCustomer);

    /**
     * Updates the given SkyLink Customer with the information from the Magento Customer.
     *
     * @param SkyLinkCustomer   $skyLinkCustomer
     * @param CustomerInterface $magentoCustomer
     */
    public function updateSkyLinkCustomer(SkyLinkCustomer $skyLinkCustomer, CustomerInterface $magentoCustomer);
}
