<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;

interface MagentoOrderAddressExtractorInterface
{
    /**
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     */
    public function extractBillingAddress(OrderInterface $magentoOrder);

    /**
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     */
    public function extractShippingAddress(OrderInterface $magentoOrder);
}
