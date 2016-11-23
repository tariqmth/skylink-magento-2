<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;

interface SkyLinkCustomerIdServiceInterface
{
    public function determineSkyLinkCustomerId(OrderInterface $magentoOrder);
}
