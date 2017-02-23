<?php

namespace RetailExpress\SkyLink\Api\Customers;

use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroup as SkyLinkPriceGroup;

interface MagentoCustomerGroupServiceInterface
{
    /**
     * Creates a Magento Customer Group from the given SkyLink Price Group.
     *
     * @param SkyLinkPriceGroup $skyLinkPriceGroup
     *
     * @return \Magento\Customer\Api\Data\GroupInterface
     */
    public function createMagentoCustomerGroup(SkyLinkPriceGroup $skyLinkPriceGroup);
}
