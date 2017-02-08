<?php

namespace RetailExpress\SkyLink\Api\Outlets;

use RetailExpress\SkyLink\Model\Outlets\PickupGroup;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;

interface SkyLinkOutletRepositoryInterface
{
    /**
     * @return RetailExpress\SkyLink\Sdk\Outlets\Outlet[]
     */
    public function getList();

    /**
     * @todo should this be here? It's specific to shipping wheras this repository is not...
     *
     * @return RetailExpress\SkyLink\Sdk\Outlets\Outlet[]
     */
    public function getListForPickupGroup(PickupGroup $pickupGroup);

    /**
     * Saves the given Outlet.
     *
     * @param SkyLinkOutlet $skyLinkOutlet
     */
    public function save(SkyLinkOutlet $skyLinkOutlet);
}
