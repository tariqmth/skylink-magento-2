<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderItemInterface;

class MagentoOrderItemNotProductBasedException extends LocalizedException
{
    public static function withMagentoOrderItem(OrderItemInterface $magentoOrderItem)
    {
        return new self(__(
            'Magento Order Item #%1 is not based on a Magento Product, cannot use in order.',
            $magentoOrderItem->getItemId()
        ));
    }
}
