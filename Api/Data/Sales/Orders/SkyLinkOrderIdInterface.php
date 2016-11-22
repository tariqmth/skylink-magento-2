<?php

namespace RetailExpress\SkyLink\Api\Data\Sales\Orders;

use Magento\Framework\Api\ExtensionAttributesInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

interface SkyLinkOrderIdInterface extends ExtensionAttributesInterface
{
    /**
     * Gets the SkyLink Order ID.
     *
     * @return SkyLinkOrderId
     */
    public function getSkyLinkOrderId();

    /**
     * Sets the SkyLink Order ID.
     *
     * @param SkyLinkOrderId $skyLinkOrderId
     */
    public function setSkyLinkOrderId(SkyLinkOrderId $skyLinkOrderId);
}
