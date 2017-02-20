<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Sales\Shipments;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use RetailExpress\SkyLink\Model\Sales\Shipments\ShipmentExtensionAttributes;
use RetailExpress\SkyLink\Model\Sales\Shipments\ShipmentFulfillmentBatchHelper;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\BatchId as SkyLinkFulfillmentBatchId;

class ShipmentRepositoryPlugin
{
    use ShipmentExtensionAttributes;
    use ShipmentFulfillmentBatchHelper;

    public function __construct(
        ResourceConnection $resourceConnection,
        ShipmentExtensionFactory $shipmentExtensionFactory
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
    }

    public function afterGet(ShipmentRepositoryInterface $subject, ShipmentInterface $magentoShipment)
    {
        $skyLinkFulfillmentBatchIdString = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getShipmentsFulfillmentBatchesTable(), 'skylink_fulfillment_batch_id')
                ->where('magento_shipment_id = ?', $magentoShipment->getEntityId())
        );

        $skyLinkFulfillmentBatchIdString = 'aoo';

        if (false !== $skyLinkFulfillmentBatchIdString) {
            /* @var \Magento\Sales\Api\Data\ShipmentExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->getShipmentExtensionAttributes($magentoShipment);
            $extendedAttributes->setSkylinkFulfillmentBatchId(
                new SkyLinkFulfillmentBatchId($skyLinkFulfillmentBatchIdString)
            );
        }

        return $magentoShipment;
    }

    public function afterSave(ShipmentRepositoryInterface $subject, ShipmentInterface $magentoShipment)
    {
        /* @var \Magento\Sales\Api\Data\ShipmentExtensionInterface $extendedAttributes */
        $extendedAttributes = $this->getShipmentExtensionAttributes($magentoShipment);
        $skyLinkFulfillmentBatchId = $extendedAttributes->getSkylinkFulfillmentBatchId();

        if (null === $skyLinkFulfillmentBatchId) {
            return $magentoShipment;
        }

        $magentoShipmentId = $magentoShipment->getEntityId();

        // Update
        if (true === $this->mappingExists($magentoShipmentId)) {
            $this->connection->update(
                $this->getShipmentsFulfillmentBatchesTable(),
                [
                    'skylink_fulfillment_batch_id' => $skyLinkFulfillmentBatchId,
                ],
                [
                    'magento_shipment_id = ?' => $magentoShipmentId,
                ]
            );

        // Create
        } else {
            $this->connection->insert(
                $this->getShipmentsFulfillmentBatchesTable(),
                [
                    'skylink_fulfillment_batch_id' => $skyLinkFulfillmentBatchId,
                    'magento_shipment_id' => $magentoShipmentId,
                ]
            );
        }
    }

    private function mappingExists($magentoShipmentId)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getShipmentsFulfillmentBatchesTable())
                ->where('magento_shipment_id = ?', $magentoShipmentId)
        );
    }
}
