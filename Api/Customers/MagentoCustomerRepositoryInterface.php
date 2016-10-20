<?php

namespace RetailExpress\SkyLink\Api\Customers;

use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;

interface MagentoCustomerRepositoryInterface
{
    /**
     * Finds an existing Customer within Magento that matches the given SkyLink Customer ID.
     *
     * @param SkyLinkCustomerId $skyLinkCustomerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Customers\TooManyCustomerMatchesException
     */
    public function findBySkyLinkCustomerId(SkyLinkCustomerId $skyLinkCustomerId);
}
