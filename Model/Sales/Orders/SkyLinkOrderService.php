<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderServiceInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;

class SkyLinkOrderService implements SkyLinkOrderServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function placeSkyLinkOrder(SkyLinkOrder $skyLinkOrder, OrderInterface $magentoOrder)
    {
        //
    }
}
