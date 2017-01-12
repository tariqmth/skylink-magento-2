<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkorderId;

class SkyLinkOrderAlreadyPlacedForMagentoOrderException extends LocalizedException
{
    public static function withMagentoOrderAndSkyLinkOrderId(
        OrderInterface $magentoOrder,
        SkyLinkorderId $skyLinkOrderId
    ) {
        return new self(__(
            'A SkyLink Order #%s has already been placed for Magento Order #%s.',
            $skyLinkOrderId,
            $magentoOrder->getIncrementId()
        ));
    }
}
