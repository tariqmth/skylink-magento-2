<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class MagentoOrderStateNotMappedException extends LocalizedException
{
    public static function withOrder(OrderInterface $magentoOrder)
    {
        return new self(__(
            'Order #%1\'s state "%2" is cannot be mapped to a SkyLink Order Status.',
            $magentoOrder->getIncrementId(),
            $magentoOrder->getState()
        ));
    }
}
