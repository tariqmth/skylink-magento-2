<?php

namespace RetailExpress\SkyLink\Commands\Customers;

/**
 * @see \RetailExpress\SkyLink\Commands\Catalogue\Attributes\EdsAttributeOptionIdWorkaround
 */
trait EdsPriceGroupIdWorkaround
{
    /**
     * The Price Group ID that was used to trigger this command (used for EDS observers to hook into).
     *
     * @var int
     */
    public $skyLinkPriceGroupId;
}
