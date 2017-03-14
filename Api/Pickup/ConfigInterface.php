<?php

namespace RetailExpress\SkyLink\Api\Pickup;

use RetailExpress\SkyLink\Model\Pickup\PickupGroup;

interface ConfigInterface
{
    /**
     * Get the Outlets configured on a website level for the given Pickup Group.
     *
     * @param PickupGroup $pickupGroup
     * @param string      $websiteCode
     *
     * @return \RetailExpress\SkyLink\Sdk\Outlets\OutletId[]
     */
    public function getOutletIdsForWebsite(PickupGroup $pickupGroup, $websiteCode);
}
