<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;

interface SkyLinkOrderServiceInterface
{
    /**
     * Places the SkyLink Order in Retail Express and then updates the
     * Magento Order accordingly with the ID of the SkyLink Order.
     *
     * @param SkyLinkOrder   $skyLinkOrder
     * @param OrderInterface $magentoOrder
     */
    public function placeSkyLinkOrder(SkyLinkOrder $skyLinkOrder, OrderInterface $magentoOrder);
}
