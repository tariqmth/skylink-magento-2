<?php

namespace RetailExpress\SkyLink\Model\Products;

trait MagentoAttributeSet
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getAttributeSetsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_attribute_sets');
    }
}
