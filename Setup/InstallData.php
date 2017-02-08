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
use RetailExpress\SkyLink\Model\Eav\Entity\Attribute\Source\PickupGroup as PickupGroupSourceModel;
use RetailExpress\SkyLink\Model\Outlets\PickupGroup;
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
        $this->addPickupGroupToProducts($eavSetup);
    }

    private function addSkyLinkCustomerIdToCustomers(EavSetup $eavSetup)
    {
        $attributeCode = 'skylink_customer_id';

        $eavSetup->addAttribute(
            Customer::ENTITY,
            $attributeCode,
            [
                'label' => 'SkyLink Customer ID',
                'required' => false,
                'system' => false,
                'user_defined' => true,
            ]
        );

        $this
            ->eavConfig
            ->getAttribute(Customer::ENTITY, $attributeCode)
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $this->addAttributeToDefaultGroupInAllSets($eavSetup, $attributeCode, Customer::ENTITY);
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
                'user_defined' => true,
            ]
        );

        $this->addAttributeToDefaultGroupInAllSets($eavSetup, $attributeCode, Product::ENTITY);
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
                        'user_defined' => true,
                        'filterable' => 1, // Filterable (with results) @todo look for constant
                    ]
                );
            }

            $this->addAttributeToDefaultGroupInAllSets($eavSetup, $magentoAttributeCode, Product::ENTITY);
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
        $this->addAttributeToDefaultGroupInAllSets($eavSetup, 'manufacturer', Product::ENTITY);
    }

    private function addPickupGroupToProducts(EavSetup $eavSetup)
    {
        $attributeCode = 'pickup_group';
        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'label' => 'Pickup Group',
                'required' => false,
                'input' => 'select',
                'source' => PickupGroupSourceModel::class,
                'default' => (string) PickupGroup::getDefault(),
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
