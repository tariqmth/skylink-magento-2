<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

trait InstallCatalogueSchema
{
    private function installCatalogueSchema(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        // Create Attribute mappings table
        $attributesTable = 'retail_express_skylink_attributes';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($attributesTable))
            ->addColumn(
                'skylink_attribute_code',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false, 'primary' => true],
                'SkyLink Attribute Code'
            )
            ->addColumn(
                'magento_attribute_code',
                DdlTable::TYPE_VARCHAR,
                255,
                ['nullable' => false],
                'Magento Attribute Code'
            );

        $installer->getConnection()->createTable($table);

        // Create Attribute Set mappings table
        $attributeSetsTable = 'retail_express_skylink_attribute_sets';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($attributeSetsTable))
            ->addColumn(
                'skylink_product_type_id',
                DdlTable::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'SkyLink Product Type ID'
            )
            ->addColumn(
                'magento_attribute_set_id',
                DdlTable::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Magento Attribute Set ID'
            )
            ->addForeignKey(
                $installer->getFkName($attributesTable, 'magento_attribute_set_id', 'eav_attribute_set', 'attribute_set_id'),
                'magento_attribute_set_id',
                'eav_attribute_set',
                'attribute_set_id',
                DdlTable::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }
}
