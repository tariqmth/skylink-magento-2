<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

trait MagentoAttribute
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getAttributesTable()
    {
        return $this->connection->getTableName('retail_express_skylink_attributes');
    }
}
