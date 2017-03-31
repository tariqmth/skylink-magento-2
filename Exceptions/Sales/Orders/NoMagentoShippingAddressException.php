<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class NoMagentoShippingAddressException extends LocalizedException
{
    public static function withMagentoOrder(OrderInterface $magentoOrder)
    {
        return new self(__(
            'Magento Order #%1 does not have a shipping address persisted for it.',
            $magentoOrder->getIncrementId()
        ));
    }
}
