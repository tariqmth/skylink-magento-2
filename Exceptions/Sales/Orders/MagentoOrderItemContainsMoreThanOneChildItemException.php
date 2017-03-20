<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderItemInterface;

class MagentoOrderItemContainsMoreThanOneChildItemException extends LocalizedException
{
    public static function withMagentoOrderItem(OrderItemInterface $magentoOrderItem)
    {
        return new self(__(
            'Magento Order Item #%1 contains more than one child item which is incompatible with SkyLink (e.g. an Order Item build from a Product Bundle).',
            $magentoOrderItem->getItemId()
        ));
    }
}
