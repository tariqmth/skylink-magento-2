<?php

namespace RetailExpress\SkyLink\Api\Pickup;

use RetailExpress\SkyLink\Model\Pickup\PickupGroup;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;

interface PickupOutletRepositoryInterface
{
    /**
     * Get a list of SkyLink Outlets to use for the given Pickup Group
     *
     * @param PickupGroup $pickupGrouip
     *
     * @return SkyLinkOutlet[]
     */
    public function getListForPickupGroup(PickupGroup $pickupGroup);
}
