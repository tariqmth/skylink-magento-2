<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface as DbAdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $this->installEdsSchema($setup, $context);
        $this->installCatalogueSchema($setup, $context);
    }

    private function installEdsSchema(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        // Create EDS Change Sets table
        $changeSetsTable = 'retail_express_skylink_eds_change_sets';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($changeSetsTable))
            ->addColumn(
                'change_set_id',
                DdlTable::TYPE_TEXT,
                36,
                ['nullable' => false, 'primary' => true],
                'Change Set ID'
            )
            ->addColumn(
                'created_at',
                DdlTable::TYPE_TIMESTAMP,
                '150',
                ['nullable' => false, 'default' => DdlTable::TIMESTAMP_INIT],
                'Created At'
            );

        $installer->getConnection()->createTable($table);

        // Create EDS Change Sets entity IDs
        $changeSetEntitiesTable = 'retail_express_skylink_eds_change_set_entities';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($changeSetEntitiesTable))
            ->addColumn(
                'change_set_id',
                DdlTable::TYPE_TEXT,
                36,
                ['nullable' => false],
                'Change Set ID'
            )
            ->addColumn(
                'entity_type',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Entity Type'
            )
            ->addColumn(
                'entity_id',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Entity ID'
            )
            ->addColumn(
                'processed_at',
                DdlTable::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Processed At'
            )
            ->addIndex(
                $installer->getIdxName($changeSetEntitiesTable, ['change_set_id', 'entity_type', 'entity_id']),
                ['change_set_id', 'entity_type', 'entity_id'],
                ['type' => DbAdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName($changeSetEntitiesTable, 'change_set_id', $changeSetsTable, 'change_set_id'),
                'change_set_id',
                $changeSetsTable,
                'change_set_id',
                DdlTable::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

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
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Magento Attribute Code'
            );

        $installer->getConnection()->createTable($table);

        // Create Attribute Option mappings table
        $attributeOptionsTable = 'retail_express_skylink_attribute_options';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($attributeOptionsTable))
            ->addColumn(
                'skylink_attribute_code',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false],
                'SkyLink Attribute Code'
            )
            ->addColumn(
                'skylink_attribute_option_id',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false],
                'SkyLink Attribute Option ID'
            )
            ->addColumn(
                'magento_attribute_option_id',
                DdlTable::TYPE_INTEGER,
                10,
                ['nullable' => false, 'unsigned' => true],
                'Magento Attribute Option ID'
            )
            ->addIndex(
                $installer->getIdxName(
                    $attributeOptionsTable,
                    ['skylink_attribute_code', 'skylink_attribute_option_id'],
                    DbAdapterInterface::INDEX_TYPE_PRIMARY
                ),
                ['skylink_attribute_code', 'skylink_attribute_option_id'],
                DbAdapterInterface::INDEX_TYPE_PRIMARY
            )
            ->addForeignKey(
                $installer->getFkName(
                    $attributeOptionsTable,
                    'skylink_attribute_code',
                    $attributesTable,
                    'skylink_attribute_code'
                ),
                'skylink_attribute_code',
                $attributesTable,
                'skylink_attribute_code',
                DdlTable::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    $attributeOptionsTable,
                    'magento_attribute_option_id',
                    'eav_attribute_option',
                    'option_id'
                ),
                'magento_attribute_option_id',
                'eav_attribute_option',
                'option_id',
                DdlTable::ACTION_CASCADE
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
