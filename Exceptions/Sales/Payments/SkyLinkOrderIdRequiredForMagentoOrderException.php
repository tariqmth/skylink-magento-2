<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Payments;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class SkyLinkOrderIdRequiredForMagentoOrderException extends LocalizedException
{
    public static function withMagentoOrder(OrderInterface $magentoOrder)
    {
        return new self(__(
            'Magento Order #%s does not have a SkyLink Order ID associated with it, cannot add payment from invoice.',
            $magentoOrder->getIncrementId()
        ));
    }
}
