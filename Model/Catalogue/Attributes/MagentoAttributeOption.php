<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

trait MagentoAttributeOption
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getAttributeOptionsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_attribute_options');
    }
}
