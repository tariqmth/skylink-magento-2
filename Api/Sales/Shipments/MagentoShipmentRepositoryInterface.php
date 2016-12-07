<?php

namespace RetailExpress\SkyLink\Api\Sales\Shipments;

use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\BatchId as SkyLinkFulfillmentBatchId;

interface MagentoShipmentRepositoryInterface
{
    public function findBySkyLinkFulfillmentBatchId(SkyLinkFulfillmentBatchId $skyLinkFulfillmentBatchId);
}
