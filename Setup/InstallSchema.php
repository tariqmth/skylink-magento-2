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
        $installer->startSetup();

        $this->installEds($setup, $context);
        $this->installAttributeMappings($setup, $context);
        $this->installPaymentMethodMappings($setup, $context);
        $this->installOrderAttributes($setup, $context);
        $this->installInvoiceAttributes($setup, $context);
        $this->installShipmentAttributes($setup, $context);
        $this->installCustomerGroupAttributes($setup, $context);
        $this->installOutletsTable($setup, $context);
        $this->installLoggingTable($setup, $context);

        $installer->endSetup();
    }

    private function installEds(SchemaSetupInterface $setup, ModuleContextInterface $context)
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

    private function installAttributeMappings(SchemaSetupInterface $setup, ModuleContextInterface $context)
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

    private function installPaymentMethodMappings(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $paymentMethodsTable = 'retail_express_skylink_payment_methods';

        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($paymentMethodsTable))
            ->addColumn(
                'magento_payment_method_code',
                DdlTable::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'skylink_payment_method_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addIndex(
                $installer->getIdxName(
                    $paymentMethodsTable,
                    ['magento_payment_method_code', 'skylink_payment_method_id'],
                    DbAdapterInterface::INDEX_TYPE_PRIMARY
                ),
                ['magento_payment_method_code', 'skylink_payment_method_id'],
                DbAdapterInterface::INDEX_TYPE_PRIMARY
            );

        $installer->getConnection()->createTable($table);
    }

    private function installOrderAttributes(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $ordersTable = 'retail_express_skylink_orders';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($ordersTable))
            ->addColumn(
                'magento_order_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'skylink_order_id',
                DdlTable::TYPE_TEXT,
                11,
                ['nullable' => false]
            )
            ->addColumn(
                'sales_channel_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addIndex(
                $installer->getIdxName(
                    $ordersTable,
                    ['magento_order_id', 'skylink_order_id', 'sales_channel_id'],
                    DbAdapterInterface::INDEX_TYPE_PRIMARY
                ),
                ['magento_order_id', 'skylink_order_id', 'sales_channel_id'],
                DbAdapterInterface::INDEX_TYPE_PRIMARY
            )
            ->addForeignKey(
                $installer->getFkName($ordersTable, 'magento_order_id', 'sales_order', 'entity_id'),
                'magento_order_id',
                'sales_order',
                'entity_id',
                DdlTable::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

    private function installInvoiceAttributes(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $invoicesPaymentsTable = 'retail_express_skylink_invoices_payments';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($invoicesPaymentsTable))
            ->addColumn(
                'magento_invoice_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'skylink_payment_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addIndex(
                $installer->getIdxName(
                    $invoicesPaymentsTable,
                    ['magento_invoice_id', 'skylink_payment_id'],
                    DbAdapterInterface::INDEX_TYPE_PRIMARY
                ),
                ['magento_invoice_id', 'skylink_payment_id'],
                DbAdapterInterface::INDEX_TYPE_PRIMARY
            )
            ->addForeignKey(
                $installer->getFkName($invoicesPaymentsTable, 'magento_invoice_id', 'sales_invoice', 'entity_id'),
                'magento_invoice_id',
                'sales_invoice',
                'entity_id',
                DdlTable::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

    private function installShipmentAttributes(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $shipmentsFulfillmentBatchesTable = 'retail_express_skylink_shipments_fufillment_batches';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($shipmentsFulfillmentBatchesTable))
            ->addColumn(
                'magento_shipment_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'skylink_fulfillment_batch_id',
                DdlTable::TYPE_TEXT,
                32,
                ['nullable' => false]
            )
            ->addIndex(
                $installer->getIdxName(
                    $shipmentsFulfillmentBatchesTable,
                    ['magento_shipment_id', 'skylink_fulfillment_batch_id'],
                    DbAdapterInterface::INDEX_TYPE_PRIMARY
                ),
                ['magento_shipment_id', 'skylink_fulfillment_batch_id'],
                DbAdapterInterface::INDEX_TYPE_PRIMARY
            )
            ->addForeignKey(
                $installer->getFkName($shipmentsFulfillmentBatchesTable, 'magento_shipment_id', 'sales_shipment', 'entity_id'),
                'magento_shipment_id',
                'sales_shipment',
                'entity_id',
                DdlTable::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

    private function installCustomerGroupAttributes(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $customerGroupsPriceGroupsTable = 'retail_express_skylink_customer_groups_price_groups';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($customerGroupsPriceGroupsTable))
            ->addColumn(
                'magento_customer_group_id',
                DdlTable::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'skylink_price_group_type',
                DdlTable::TYPE_TEXT,
                8,
                ['nullable' => false]
            )
            ->addColumn(
                'skylink_price_group_id',
                DdlTable::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addIndex(
                $installer->getIdxName(
                    $customerGroupsPriceGroupsTable,
                    ['magento_customer_group_id', 'skylink_price_group_type', 'skylink_price_group_id'],
                    DbAdapterInterface::INDEX_TYPE_PRIMARY
                ),
                ['magento_customer_group_id', 'skylink_price_group_type', 'skylink_price_group_id'],
                DbAdapterInterface::INDEX_TYPE_PRIMARY
            )
            ->addIndex(
                $installer->getIdxName(
                    $customerGroupsPriceGroupsTable,
                    ['magento_customer_group_id', 'skylink_price_group_type'],
                    DbAdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['magento_customer_group_id', 'skylink_price_group_type'],
                DbAdapterInterface::INDEX_TYPE_UNIQUE
            )
            ->addForeignKey(
                $installer->getFkName(
                    $customerGroupsPriceGroupsTable,
                    'magento_customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'magento_customer_group_id',
                'customer_group',
                'customer_group_id',
                DdlTable::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

    private function installOutletsTable(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $outletsTable = 'retail_express_skylink_outlets';

        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($outletsTable))
            ->addColumn(
                'id',
                DdlTable::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'name',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => false]
            )
            ->addColumn(
                'address_line_1',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'address_line_2',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'address_line_3',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'address_city',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'address_state',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'address_postcode',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'address_country_code',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'phone_number',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            )
            ->addColumn(
                'fax_number',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => true]
            );

        $installer->getConnection()->createTable($table);
    }

    private function installLoggingTable(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $loggingTable = 'retail_express_skylink_logs';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($loggingTable))
            ->addColumn(
                'id',
                DdlTable::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'channel',
                DdlTable::TYPE_TEXT,
                '255',
                ['nullable' => false]
            )
            ->addColumn(
                'level',
                DdlTable::TYPE_INTEGER,
                '3',
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'message',
                DdlTable::TYPE_TEXT,
                '64k'
            )
            ->addColumn(
                'context',
                DdlTable::TYPE_TEXT,
                '64k'
            )
            ->addColumn(
                'logged_at',
                DdlTable::TYPE_TIMESTAMP,
                '150',
                ['nullable' => false, 'default' => DdlTable::TIMESTAMP_INIT],
                'Logged At'
            )
            ->addColumn(
                'captured',
                DdlTable::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0]
            );

        $installer->getConnection()->createTable($table);
    }
}
