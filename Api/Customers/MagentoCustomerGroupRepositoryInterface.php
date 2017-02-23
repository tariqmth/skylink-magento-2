<?php

namespace RetailExpress\SkyLink\Api\Customers;

use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

interface MagentoCustomerGroupRepositoryInterface
{
    /**
     * Finds a Magento Customer Group by the given SkyLink Price Group Key.
     *
     * @param SkyLinkPriceGroupKey $skyLinkPriceGroupKey
     *
     * @return \Magento\Customer\Api\Data\GroupInterface|null
     */
    public function findBySkyLinkPriceGroupKey(SkyLinkPriceGroupKey $skyLinkPriceGroupKey);
}
