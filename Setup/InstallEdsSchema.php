<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface as DbAdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

trait InstallEdsSchema
{
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
}
