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
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

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
        // $this->addSkyLinkProductIdsToProducts($eavSetup);
        $this->addSkyLinkAttributeCodesToProducts($eavSetup);
    }

    private function addSkyLinkCustomerIdToCustomers(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'skylink_customer_id',
            [
                'label' => 'SkyLink Customer ID',
                'required' => false,
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
        $attributeCode = 'skylink_product_id';

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'label' => 'SkyLink Product ID',
                'required' => false,
            ]
        );

        $this->addAttributeToDefaultGroupInAllSets($eavSetup, $attributeCode);
    }

    private function addSkyLinkAttributeCodesToProducts(EavSetup $eavSetup)
    {
        array_map(function ($skyLinkAttributeCodeString) use ($eavSetup) {
            $skyLinkAttributeCode = SkyLinkAttributeCode::get($skyLinkAttributeCodeString);

            $eavSetup->addAttribute(
                Product::ENTITY,
                $skyLinkAttributeCode->getValue(),
                [
                    'label' => $skyLinkAttributeCode->getLabel(),
                    'required' => false,

                ]
            );
        }, SkyLinkAttributeCode::getPredefined());
    }

    private function addAttributeToDefaultGroupInAllSets(EavSetup $eavSetup, $attributeCode)
    {
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
