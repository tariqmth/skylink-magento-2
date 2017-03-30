<?php

namespace RetailExpress\SkyLink\Api\Sales\Shipments;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;

interface MagentoShipmentBuilderInterface
{
    /**
     * @return Shipment
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Sales\Orders\FulfillmentShippingMoreThanAvailableException
     */
    public function buildFromMagentoOrderAndGroupsOfMagentoOrderItemsAndSkyLinkFulfillments(
        Order $magentoOrder,
        array $groups
    );
}
