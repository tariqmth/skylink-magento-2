<?php

namespace RetailExpress\SkyLink\Api\Customers;

use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

interface MagentoCustomerGroupRepositoryInterface
{
    /**
     * Gets a list of all mapped price group keys.
     *
     * @return SkyLinkPriceGroupKey[]
     */
    public function getListOfMappedPriceGroupKeys();

    /**
     * Finds a Magento Customer Group by the given SkyLink Price Group Key.
     *
     * @param SkyLinkPriceGroupKey $skyLinkPriceGroupKey
     *
     * @return \Magento\Customer\Api\Data\GroupInterface|null
     */
    public function findBySkyLinkPriceGroupKey(SkyLinkPriceGroupKey $skyLinkPriceGroupKey);
}
