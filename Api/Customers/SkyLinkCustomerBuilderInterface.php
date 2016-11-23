<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;

interface SkyLinkCustomerBuilderInterface
{
    /**
     * Build a SkyLink Customer instance from the given Magento Customer.
     *
     * @param CustomerInterface $magentoCustomer
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\Customer
     */
    public function buildFromMagentoCustomer(CustomerInterface $magentoCustomer);
}
