<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

trait OrderHelper
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getOrdersTable()
    {
        return $this->connection->getTableName('retail_express_skylink_orders');
    }
}
