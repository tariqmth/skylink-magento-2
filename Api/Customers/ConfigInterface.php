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
}
