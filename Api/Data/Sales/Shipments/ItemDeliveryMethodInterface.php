<?php

namespace RetailExpress\SkyLink\Api\Data\Sales\Shipments;

interface ItemDeliveryMethodInterface
{
    /**
     * Return the item delivery method for this shipping method.
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Orders\ItemDeliveryMethod
     */
    public function getItemDeliveryMethod();
}
