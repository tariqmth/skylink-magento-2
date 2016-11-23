<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use Magento\Sales\Api\Data\OrderItemInterface;

interface SkyLinkOrderItemBuilderInterface
{
    /**
     * Builds a SkyLink Order Item from the given Magento Order Item.
     *
     * @param OrderItemInterface $magentoOrderItem
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Orders\Item
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotLinkedToSkyLinkProductException
     * @throws \RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotProductBasedException
     */
    public function buildFromMagentoOrderItem(OrderItemInterface $magentoOrderItem);
}
