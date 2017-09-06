<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class TooManyMagentoShippingAddressesException extends LocalizedException
{
    public static function withMagentoOrder(OrderInterface $magentoOrder)
    {
        return new self(__(
            'Magento Order #%1 has too many shipping address persisted for it.',
            $magentoOrder->getIncrementId()
        ));
    }
}
