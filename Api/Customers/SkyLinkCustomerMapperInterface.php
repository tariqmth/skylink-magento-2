<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

interface SkyLinkCustomerMapperInterface
{
    /**
     * Maps the given Magento Customer from the SkyLink Customer.
     *
     * @param SkyLinkCustomer   $skyLinkCustomer
     * @param CustomerInterface $magentoCustomer
     */
    public function mapSkyLinkCustomer(SkyLinkCustomer $skyLinkCustomer, CustomerInterface $magentoCustomer);
}
