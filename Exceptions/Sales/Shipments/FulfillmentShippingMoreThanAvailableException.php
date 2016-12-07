<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Item;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Fulfillment as SkyLinkFulfillment;

class FulfillmentShippingMoreThanAvailableException extends LocalizedException
{
    public static function withSkyLinkFulfillmentAndMagentoOrderItem(
        SkyLinkFulfillment $skyLinkFulfillment,
        Item $magentoOrderItem
    ) {
        return new self(__(
            'SkyLink Fulfillment #%1 is trying to ship a quantity %2, whereas Magento Order Item #%3 only has %4 left to ship.',
            $skyLinkFulfillment->getId(),
            $skyLinkFulfillment->getQty(),
            $magentoOrderItem->getItemId(),
            $magentoOrderItem->getQtyToShip()
        ));
    }
}
