<?php

namespace RetailExpress\SkyLink\Api\Sales\Shipments;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;

interface MagentoShipmentBuilderInterface
{
    /**
     * @return Shipment
     */
    public function buildFromMagentoOrderAndGroupsOfMagentoOrderItemsAndSkyLinkFulfillments(
        Order $magentoOrder,
        array $groups
    );
}
