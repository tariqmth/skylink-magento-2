<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface as DbAdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Upgrading to 1.2.0
        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $this->installMatrixPolicyMappings($setup, $context);
        }

        $installer->endSetup();
    }

    private function installMatrixPolicyMappings(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $matrixPoliciesTable = 'retail_express_skylink_matrix_policies';
        $table = $setup
            ->getConnection()
            ->newTable($installer->getTable($matrixPoliciesTable))
            ->addColumn(
                'skylink_product_type_id',
                DdlTable::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'SkyLink Product Type ID'
            )
            ->addColumn(
                'skylink_attribute_codes',
                DdlTable::TYPE_TEXT,
                255, // Currently the allowed attribute codes separated by a comma are only 51 characters in total
                ['nullable' => false],
                'SkyLink Attribute Codes'
            );

        $installer->getConnection()->createTable($table);
    }
}
