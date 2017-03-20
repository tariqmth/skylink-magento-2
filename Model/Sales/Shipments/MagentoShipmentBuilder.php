<?php

namespace RetailExpress\SkyLink\Model\Sales\Shipments;

use Magento\Sales\Model\Convert\Order as MagentoOrderConverter;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoShipmentBuilderInterface;

class MagentoShipmentBuilder implements MagentoShipmentBuilderInterface
{
    private $magentoOrderConverter;

    public function __construct(
        MagentoOrderConverter $magentoOrderConverter
    ) {
        $this->magentoOrderConverter = $magentoOrderConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFromMagentoOrderAndGroupsOfMagentoOrderItemsAndSkyLinkFulfillments(
        Order $magentoOrder,
        array $groups
    ) {
        /* @var Shipment $magentoShipment */
        $magentoShipment = $this->magentoOrderConverter->toShipment($magentoOrder);

        array_walk($groups, function (array $group) use ($magentoShipment) {
            list($magentoOrderItem, $skyLinkFulfillment) = $group;
            $qtyThatCanBeShipped = $magentoOrderItem->getQtyToShip();
            $fulfillmentQty = $skyLinkFulfillment->getQty()->toNative();

            if ($fulfillmentQty > $qtyThatCanBeShipped) {
                throw FulfillmentShippingMoreThanAvailableException::withSkyLinkFulfillmentAndMagentoOrderItem(
                    $skyLinkFulfillment,
                    $magentoOrderItem
                );
            }

            $magentoShipmentItem = $this->magentoOrderConverter->itemToShipmentItem($magentoOrderItem);
            $magentoShipmentItem->setQty($fulfillmentQty);

            $magentoShipment->addItem($magentoShipmentItem);
        });

        return $magentoShipment;
    }
}
