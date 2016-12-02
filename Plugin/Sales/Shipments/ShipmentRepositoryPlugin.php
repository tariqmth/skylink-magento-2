<?php

namespace RetailExpress\SkyLink\Plugin\Sales\Shipments;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use RetailExpress\SkyLink\Model\Sales\Shipments\ShipmentExtensionAttributes;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\BatchId as SkyLinkFulfillmentBatchId;

class ShipmentRepositoryPlugin
{
    use ShipmentExtensionAttributes;

    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

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

    public function afterSave(ShipmentRepositoryInterface $subject, ShipmentInterface $magentoShipment, SkyLinkFulfillmentBatchId $skyLinkFulfillmentBatchId)
    {
        $magentoShipmentId = $magentoShipment->getEntityId();

        // Update
        if (true === $this->mappingExists($magentoShipmentId, $skyLinkFulfillmentBatchId)) {
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

    private function mappingExists($magentoShipmentId, SkyLinkFulfillmentBatchId $skyLinkFulfillmentBatchId)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getShipmentsFulfillmentBatchesTable(), 'skylink_fulfillment_batch_id')
                ->where('magento_shipment_id = ?', $magentoShipmentId)
        );
    }

    private function getShipmentsFulfillmentBatchesTable()
    {
        return $this->connection->getTableName('retail_express_skylink_shipments_fufillment_batches');
    }
}
