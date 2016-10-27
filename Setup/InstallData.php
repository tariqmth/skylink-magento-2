<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    private $eavConfig;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        EavConfig $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // https://github.com/magento/magento2/issues/1238#issuecomment-105034397

        /* @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // $this->addSkyLinkCustomerIdToCustomers($eavSetup);
        $this->addSkyLinkProductIdsToProducts($eavSetup);
    }

    private function addSkyLinkCustomerIdToCustomers(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'skylink_customer_id',
            [
                // @todo optimise arguments (such as "type", "backend")
                'label' => 'SkyLink Customer ID',
                'required' => false,
                'system' => false,
                'position' => 100,
            ]
        );

        $this
            ->eavConfig
            ->getAttribute(Customer::ENTITY, 'skylink_customer_id')
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();
    }

    private function addSkyLinkProductIdsToProducts(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            'skylink_product_id',
            [
                // @todo optimise arguments (such as "type", "backend")
                'label' => 'SkyLink Product ID',
                'required' => false,
                'system' => true,
                'position' => 100,
            ]
        );

        foreach ($eavSetup->getAllAttributeSetIds() as $attributeSetId) {
            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                $eavSetup->getDefaultAttributeGroupId($attributeSetId), // @todo should this be another group?
                'skylink_product_id'
            );
        }
    }
}
