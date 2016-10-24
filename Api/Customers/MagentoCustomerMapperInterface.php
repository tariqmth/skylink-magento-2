<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

interface MagentoCustomerMapperInterface
{
    /**
     * Maps the given Magento Customer from the SkyLink Customer.
     *
     * @param CustomerInterface $magentoCustomer
     * @param SkyLinkCustomer   $skyLinkCustomer
     */
    public function mapMagentoCustomer(CustomerInterface $magentoCustomer, SkyLinkCustomer $skyLinkCustomer);
}
