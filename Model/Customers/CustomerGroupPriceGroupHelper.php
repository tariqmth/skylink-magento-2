<?php

namespace RetailExpress\SkyLink\Model\Customers;

trait CustomerGroupPriceGroupHelper
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getCustomerGroupsPriceGroupsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_customer_groups_price_groups');
    }
}
