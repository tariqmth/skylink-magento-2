<?php

namespace RetailExpress\SkyLink\Model\Sales\Shipments;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\BatchId as SkyLinkFulfillmentBatchId;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoShipmentRepositoryInterface;

class MagentoShipmentRepository implements MagentoShipmentRepositoryInterface
{
    use ShipmentFulfillmentBatchHelper;

    private $baseMagentoShipmentRepository;

    public function __construct(
        ResourceConnection $resourceConnection,
        ShipmentRepositoryInterface $baseMagentoShipmentRepository
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->baseMagentoShipmentRepository = $baseMagentoShipmentRepository;
    }

    public function findBySkyLinkFulfillmentBatchId(SkyLinkFulfillmentBatchId $skyLinkFulfillmentBatchId)
    {
        $magentoShipmentId = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getShipmentsFulfillmentBatchesTable(), 'magento_shipment_id')
                ->where('skylink_fulfillment_batch_id = ?', $skyLinkFulfillmentBatchId)
        );

        if (false === $magentoShipmentId) {
            return null;
        }

        return $this->baseMagentoShipmentRepository->get($magentoShipmentId);
    }
}
