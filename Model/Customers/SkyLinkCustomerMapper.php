<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerMapperInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

class SkyLinkCustomerMapper implements SkyLinkCustomerMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapSkyLinkCustomer(SkyLinkCustomer $skyLinkCustomer, CustomerInterface $magentoCustomer)
    {
        //
    }
}
