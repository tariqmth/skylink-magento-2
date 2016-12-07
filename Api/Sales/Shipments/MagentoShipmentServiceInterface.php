<?php

namespace RetailExpress\SkyLink\Api\Sales\Shipments;

use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Batch as SkyLinkFulfillmentBatch;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;

interface MagentoShipmentServiceInterface
{
    public function createMagentoShipmentFromSkyLinkFulfillmentBatch(
        SkyLinkOrder $skyLinkOrder,
        SkyLinkFulfillmentBatch $skyLinkFulfillmentBatch
    );
}
