<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /* @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // Upgrading to 1.2.0
        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $this->addQtyAvailableToProducts($eavSetup);
        }

        $setup->endSetup();
    }

    private function addQtyAvailableToProducts(EavSetup $eavSetup)
    {
        $attributeCode = 'qty_available';

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'label' => 'Qty Available',
                'required' => false,
                'user_defined' => true,
            ]
        );

        $this->addAttributeToDefaultGroupInAllSets($eavSetup, $attributeCode, Product::ENTITY);
    }

    private function addAttributeToDefaultGroupInAllSets(EavSetup $eavSetup, $magentoAttributeCode, $entityType)
    {
        foreach ($eavSetup->getAllAttributeSetIds($entityType) as $attributeSetId) {
            $eavSetup->addAttributeToGroup(
                $entityType,
                $attributeSetId,
                $eavSetup->getDefaultAttributeGroupId($entityType, $attributeSetId),
                $magentoAttributeCode
            );
        }
    }
}
