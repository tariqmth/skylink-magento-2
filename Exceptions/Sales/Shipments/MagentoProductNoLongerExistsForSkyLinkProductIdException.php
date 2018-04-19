<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Shipments;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Item;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Fulfillment as SkyLinkFulfillment;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class MagentoProductNoLongerExistsForSkyLinkProductIdException extends LocalizedException
{
    public static function withSkyLinkFulfillmentAndProductId(
        SkyLinkFulfillment $skyLinkFulfillment,
        SkyLinkProductId $skyLinkProductId
    ) {
        return new self(__(
            'SkyLink Fulfillment #%1 is associated wtih SkyLink Product #%2, however this no longer exists in Magento.',
            $skyLinkFulfillment->getId(),
            $skyLinkProductId
        ));
    }
}
