<?php

namespace RetailExpress\SkyLink\Magento2\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;

interface MagentoCustomerServiceInterface
{
    /**
     * Register a new Magento Customer from the given SkyLink Customer.
     *
     * @param SkyLinkCustomer $skyLinkCustomer
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
