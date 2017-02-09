<?php

namespace RetailExpress\SkyLink\Api\Outlets;

use Magento\Sales\Model\Order as MagentoOrder;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;

interface PickupManagementInterface
{
    /**
     * Get the shipping carrier code for pickup.
     *
     * @return string
     */
    public function getMagentoShippingCarrierCode();

    /**
     * Get the shipping method code for the given SkyLink Outlet.
     *
     * @param SkyLinkOutlet $skyLinkOutlet
     *
     * @return string
     */
    public function getMagentoShippingMethodCode(SkyLinkOutlet $skyLinkOutlet);

    /**
     * Get the shipping method title for the given SkyLink Outlet.
     *
     * @param SkyLinkOutlet $skyLinkOutlet
     *
     * @return string
     */
    public function getMagentoShippingMethodTitle(SkyLinkOutlet $skyLinkOutlet);

    /**
     * Determines the SkyLink Outlet that the Magento Order should be picked up from, if any.
     *
     * @return SkyLinkOutlet|null A SkyLink Outlet if applicable
     */
    public function determineSkyLinkOutletToPickupFrom(MagentoOrder $magentoOrder);
}
