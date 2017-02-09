<?php

namespace RetailExpress\SkyLink\Api\Outlets;

use RetailExpress\SkyLink\Model\Outlets\PickupGroup;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;

interface SkyLinkOutletRepositoryInterface
{
    /**
     * @return RetailExpress\SkyLink\Sdk\Outlets\Outlet[]
     */
    public function getList();

    /**
     * @todo should this be here? It's specific to shipping wheras this repository is not...
     *
     * @return SkyLinkOutlet[]
     */
    public function getListForPickupGroup(PickupGroup $pickupGroup);

    /**
     * Gets the given SkyLink Outlet.
     *
     * @param SkyLinkOutletId $skyLinkOutletId
     *
     * @return SkyLinkOutlet
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(SkyLinkOutletId $skyLinkOutletId);

    /**
     * Saves the given Outlet.
     *
     * @param SkyLinkOutlet $skyLinkOutlet
     */
    public function save(SkyLinkOutlet $skyLinkOutlet);
}
