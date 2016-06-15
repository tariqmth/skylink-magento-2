<?php

namespace RetailExpress\SkyLinkMagento2\Setup;

use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritDoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable('skylink_jobs'))
            ->addColumn(
                'job_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Job ID'
            )
            ->addColumn(
                'queue',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Queue'
            )
            ->addColumn(
                'payload',
                DdlTable::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Payload'
            )
            ->addColumn(
                'attempts',
                DdlTable::TYPE_INTEGER,
                null,
                ['unsigned' => true,  'nullable' => false, 'default' => 0],
                'Attempts'
            )
            ->addColumn(
                'reserved',
                DdlTable::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Reserved'
            )
            ->addColumn(
                'reserved_at',
                DdlTable::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Reserved At'
            )
            ->addColumn(
                'available_at',
                DdlTable::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Available At'
            )
            ->addColumn(
                'created_at',
                DdlTable::TYPE_TIMESTAMP,
                '150',
                ['nullable' => false, 'default' => DdlTable::TIMESTAMP_INIT],
                'Created At'
            );

        $installer->getConnection()->createTable($table);
    }
}
