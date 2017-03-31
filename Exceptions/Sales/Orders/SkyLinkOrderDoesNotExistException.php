<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkorderId;

class SkyLinkOrderDoesNotExistException extends LocalizedException
{
    public static function withSkyLinkOrderId(SkyLinkorderId $skyLinkOrderId)
    {
        return new self(__(
            'SkyLink Order #%1 does not exist.',
            $skyLinkOrderId
        ));
    }
}
