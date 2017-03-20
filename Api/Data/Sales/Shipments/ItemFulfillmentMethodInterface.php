<?php

namespace RetailExpress\SkyLink\Api\Data\Sales\Shipments;

interface ItemFulfillmentMethodInterface
{
    /**
     * Return the item fulfillment method for this shipping method.
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Orders\ItemFulfillmentMethod
     */
    public function getItemFulfillmentMethod();
}
