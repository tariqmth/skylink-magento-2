<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

class NoMagentoOrderForSkyLinkOrderIdException extends LocalizedException
{
    public static function withSkyLinkOrderId(SkyLinkOrderId $skyLinkOrderId)
    {
        return new self(__(
            'SkyLink Order ID #%1 is not linked to a Magento Order.',
            $skyLinkOrderId
        ));
    }
}
