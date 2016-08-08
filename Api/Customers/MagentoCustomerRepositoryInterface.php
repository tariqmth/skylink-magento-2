<?php

namespace RetailExpress\SkyLink\Magento2\Api\Customers;

use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;

interface MagentoCustomerRepositoryInterface
{
    /**
     * Finds an existing Customer within Magento that matches the given SkyLink Customer.
     *
     * @param SkyLinkCustomer $skyLinkCustomer
     *
     * @return CustomerInterface|null
     *
     * @throws TooManyCustomerMatchesException
     */
    public function findBySkyLinkCustomerId(SkyLinkCustomerId $skyLinkCustomerId);
}
