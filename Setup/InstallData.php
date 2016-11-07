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

    /**
     * @todo rework this with \RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeRepository
     */
    private static function getDefaultAttributeMappings()
    {
        return [
            'brand' => 'manufacturer',
            'colour' => 'color',
        ];
    }

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
        /* @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $this->addSkyLinkCustomerIdToCustomers($eavSetup);
        $this->addSkyLinkProductIdsToProducts($eavSetup);
        $this->addSkyLinkAttributeCodesToProducts($eavSetup);
        $this->addManufacturerToAttributeSets($eavSetup);
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
            $magentoAttributeCode = $this->getDefaultMagentoAttributeCode($skyLinkAttributeCode);

            $hasExistingAttribute = (bool) $eavSetup
                ->getAttributeId(Product::ENTITY, $magentoAttributeCode);

            if (false === $hasExistingAttribute) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $magentoAttributeCode,
                    [
                        'label' => $skyLinkAttributeCode->getLabel(),
                        'required' => false,
                        'input' => 'select',
                    ]
                );
            }

            $this->addAttributeToDefaultGroupInAllSets($eavSetup, $magentoAttributeCode);

        }, SkyLinkAttributeCode::getConstants());
    }

    /**
     * Manufacturer is by default not exposed in the attribute sets, which make it impossible
     * to create a mapping for it. By putting into all of the attribute sets, we can ensure
     * that mapings can be created.
     *
     * @param EavSetup $eavSetup
     */
    private function addManufacturerToAttributeSets(EavSetup $eavSetup)
    {
        $this->addAttributeToDefaultGroupInAllSets($eavSetup, 'manufacturer');
    }

    private function addAttributeToDefaultGroupInAllSets(EavSetup $eavSetup, $magentoAttributeCode)
    {
        foreach ($eavSetup->getAllAttributeSetIds() as $attributeSetId) {
            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                $eavSetup->getDefaultAttributeGroupId($attributeSetId), // @todo should this be another group?
                $magentoAttributeCode
            );
        }
    }

    /**
     * @todo rework this with \RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeRepository
     *
     * @return string
     */
    private function getDefaultMagentoAttributeCode(SkyLinkAttributeCode $skyLinkAttributeCode)
    {
        $defaultAttributeMappings = self::getDefaultAttributeMappings();

        if (array_key_exists($skyLinkAttributeCode->getValue(), $defaultAttributeMappings)) {
            return $defaultAttributeMappings[$skyLinkAttributeCode->getValue()];
        }

        return $skyLinkAttributeCode->getValue();
    }
}
