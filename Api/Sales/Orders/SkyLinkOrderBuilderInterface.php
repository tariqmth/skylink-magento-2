<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;

interface SkyLinkOrderBuilderInterface
{
    /**
     * Builds a SkyLink Order from the given Magento Order.
     *
     * @param OrderInterface $magentoOrder
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Orders\Order
     */
    public function buildFromMagentoOrder(OrderInterface $magentoOrder);
}
