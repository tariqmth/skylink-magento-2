<?php

namespace RetailExpress\SkyLink\Api\Outlets;

use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;

interface SkyLinkOutletRepositoryInterface
{
    /**
     * @return RetailExpress\SkyLink\Sdk\Outlets\Outlet
     */
    public function getList();

    /**
     * Saves the given Outlet.
     *
     * @param SkyLinkOutlet $skyLinkOutlet
     */
    public function save(SkyLinkOutlet $skyLinkOutlet);
}
