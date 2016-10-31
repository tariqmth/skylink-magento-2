<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Eav\Api\Data\AttributeOptionInterface;

trait MagentoAttributeOption
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * The Magento Attribute Option Managmeent instance.
     *
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    private $magentoAttributeOptionManagement;

    /**
     * Get the Attribute Options table name.
     *
     * @return string
     */
    private function getAttributeOptionsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_attribute_options');
    }

    /**
     * @todo The ID is not exposed in the interface, this needs to change somehow. This
     * should nearly always work, but it's not the best way of going about it
     */
    private function getIdFromMagentoAttributeOption(AttributeOptionInterface $magentoAttributeOption)
    {
        return $magentoAttributeOption->getValue();
    }
}
