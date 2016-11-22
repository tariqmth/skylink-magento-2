<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderBuilderInterface;

class SkyLinkOrderBuilder implements SkyLinkOrderBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildFromMagentoOrder(OrderInterface $magentoOrder)
    {
        //
    }
}
