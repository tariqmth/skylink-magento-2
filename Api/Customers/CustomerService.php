<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;

interface CustomerService
{
    /**
     * Register a new Customer from the given Retail Express Customer.
     *
     * @param SkyLinkCustomer $retailExpressCustomer
     */
    public function registerCustomer(SkyLinkCustomer $retailExpressCustomer);

    /**
     * Updates the given Customer with the information from the Retail Express Customer.
     *
     * @param Customer        $customer
     * @param SkyLinkCustomer $retailExpressCustomer
     */
    public function updateCustomer(CustomerInterface $customer, SkyLinkCustomer $retailExpressCustomer);
}
