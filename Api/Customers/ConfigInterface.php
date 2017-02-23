<?php

namespace RetailExpress\SkyLink\Api\Customers;

interface ConfigInterface
{
    /**
     * Get the tax class id used for new customer groups.
     *
     * @return int
     */
    public function getCustomerGroupTaxClassId();

    /**
     * Get the Price Group Type to choose for Customer Groups.
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType
     */
    public function getSkyLinkPriceGroupType();

    /**
     * Gets the default Customer Group ID. Basically a wrapper for functionality contained in:
     *
     * Magento\Customer\Model::getGroupId()
     */
    public function getDefaultCustomerGroupId();
}
