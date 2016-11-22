<?php

namespace RetailExpress\SkyLink\Plugin\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $magentoOrder)
    {
        // Retrieve mapping if it exists

        return $magentoOrder;
    }

    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $magentoOrder)
    {
        // Save mapping if it does not exist

        return $magentoOrder;
    }
}
