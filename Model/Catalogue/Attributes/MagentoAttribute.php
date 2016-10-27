<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Eav\Api\Data\AttributeOptionInterface;

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

    /**
     * @todo The ID is not exposed in the interface, this needs to change somehow. This
     * should nearly always work, but it's not the best way of going about it.
     */
    private function getIdFromMagentoAttributeOption(AttributeOptionInterface $magentoAttributeOption)
    {
        return $magentoAttributeOption->getId();
    }
}
