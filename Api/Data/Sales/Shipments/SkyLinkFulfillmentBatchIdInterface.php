<?php

namespace RetailExpress\SkyLink\Api\Data\Sales\Shipments;

use Magento\Framework\Api\ExtensionAttributesInterface;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\BatchId as SkyLinkFulfillmentBatchId;

interface SkyLinkFulfillmentBatchIdInterface extends ExtensionAttributesInterface
{
    /**
     * Gets the SkyLink Fulfillment Batch ID.
     *
     * @return SkyLinkFulfillmentBatchId
     */
    public function getSkyLinkFulfillmentBatchId();

    /**
     * Sets the SkyLink Fulfillment Batch ID.
     *
     * @param SkyLinkFulfillmentBatchId $skyLinkFulfillmentBatchId
     */
    public function setSkyLinkFulfillmentBatchId(SkyLinkFulfillmentBatchId $skyLinkFulfillmentBatchId);
}
