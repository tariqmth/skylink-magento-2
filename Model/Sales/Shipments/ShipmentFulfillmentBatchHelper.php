<?php

namespace RetailExpress\SkyLink\Model\Sales\Shipments;

trait ShipmentFulfillmentBatchHelper
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getShipmentsFulfillmentBatchesTable()
    {
        return $this->connection->getTableName('retail_express_skylink_shipments_fufillment_batches');
    }
}
