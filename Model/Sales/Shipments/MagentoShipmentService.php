<?php

namespace RetailExpress\SkyLink\Model\Sales\Shipments;

use InvalidArgumentException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\ShipmentNotifier;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoOrderItemAndSkyLinkFulfillmentGrouperInterface;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoShipmentBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoShipmentServiceInterface;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Batch as SkyLinkFulfillmentBatch;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;

class MagentoShipmentService implements MagentoShipmentServiceInterface
{
    use ShipmentExtensionAttributes;

    private $magentoOrderRepository;

    private $magentoOrderItemAndSkyLinkFulfillmentGrouper;

    private $magentoShipmentBuilder;

    private $baseMagentoShipmentRepository;

    private $magentoShipmentNotifier;

    public function __construct(
        MagentoOrderRepositoryInterface $magentoOrderRepository,
        MagentoOrderItemAndSkyLinkFulfillmentGrouperInterface $magentoOrderItemAndSkyLinkFulfillmentGrouper,
        MagentoShipmentBuilderInterface $magentoShipmentBuilder,
        ShipmentRepositoryInterface $baseMagentoShipmentRepository,
        ShipmentExtensionFactory $shipmentExtensionFactory,
        ShipmentNotifier $magentoShipmentNotifier
    ) {
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->magentoOrderItemAndSkyLinkFulfillmentGrouper = $magentoOrderItemAndSkyLinkFulfillmentGrouper;
        $this->magentoShipmentBuilder = $magentoShipmentBuilder;
        $this->baseMagentoShipmentRepository = $baseMagentoShipmentRepository;
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
        $this->magentoShipmentNotifier = $magentoShipmentNotifier;
    }

    public function createMagentoShipmentFromSkyLinkFulfillmentBatch(
        SkyLinkOrder $skyLinkOrder,
        SkyLinkFulfillmentBatch $skyLinkFulfillmentBatch
    ) {
        /* @var OrderInterface $magentoOrder */
        $magentoOrder = $this->magentoOrderRepository->findBySkyLinkOrderId($skyLinkOrder->getId());

        $this->assertActualImplementationOfMagentoOrderInterface($magentoOrder);

        // We now need to group order items together with the matching fulfillment in the fulfillment batch
        $groups = $this->magentoOrderItemAndSkyLinkFulfillmentGrouper->group(
            $skyLinkOrder,
            $skyLinkFulfillmentBatch,
            $magentoOrder->getAllItems()
        );

        $magentoShipment = $this
            ->magentoShipmentBuilder
            ->buildFromMagentoOrderAndGroupsOfMagentoOrderItemsAndSkyLinkFulfillments($magentoOrder, $groups);

        /* @var \Magento\Sales\Api\Data\ShipmentExtensionInterface $extendedAttributes */
        $extendedAttributes = $this->getShipmentExtensionAttributes($magentoShipment);
        $extendedAttributes->setSkylinkFulfillmentBatchId($skyLinkFulfillmentBatch->getId()); // Note lowercase "l"

        $this->save($magentoShipment);

        return $magentoShipment;
    }

    private function assertActualImplementationOfMagentoOrderInterface(OrderInterface $magentoOrder)
    {
        // The API will return an instance of the
        if (false === $magentoOrder instanceof Order) {
            throw new RuntimeException(sprintf(
                'Due to limitations in Magento\'s API classes, we must deal with instances of %s, %s given.',
                Order::class,
                get_class($magentoOrder)
            ));
        }
    }

    /**
     * @see {{vendor}}/magento/magento2-base/dev/tests/integration/testsuite/Magento/Sales/_files/shipment.php
     */
    private function save(Shipment $magentoShipment)
    {
        $magentoShipment->register();

        $this->baseMagentoShipmentRepository->save($magentoShipment);
        $magentoShipment->getOrder()->save();
    }
}
