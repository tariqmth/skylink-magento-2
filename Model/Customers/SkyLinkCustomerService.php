<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

class SkyLinkCustomerService implements SkyLinkCustomerServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function registerSkyLinkCustomer(CustomerInterface $magentoCustomer)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function updateSkyLinkCustomer(SkyLinkCustomer $skyLinkCustomer, CustomerInterface $magentoCustomer)
    {
        //
    }
}
