<?php

namespace RetailExpress\SkyLink\Api\Sales\Shipments;

use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Batch as SkyLinkFulfillmentBatch;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;

interface MagentoOrderItemAndSkyLinkFulfillmentGrouperInterface
{
    /**
     * Takes a SkyLink Order and the Fulfillment Batch ID to use as well as an array of Magento Order Items and groups
     * Magento Order Items with SkyLink Fulfillments (from the given Batch) where they refer to the same product.
     *
     * @todo Work out how we can deal with the API classes (if methods from the models are put in there in future)
     *
     * @param SkyLinkOrder                      $skyLinkOrder
     * @param SkyLinkFulfillmentBatch           $skyLinkFulfillmentBatch
     * @param \Magento\Sales\Model\Order\Item[] $magentoOrderItems
     *
     * @return [\Magento\Sales\Model\Order\Item, \RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Fulfillment]
     *
     * @throws {@todo}
     */
    public function group(
        SkyLinkOrder $skyLinkOrder,
        SkyLinkFulfillmentBatch $skyLinkFulfillmentBatch,
        array $magentoOrderItems
    );
}
