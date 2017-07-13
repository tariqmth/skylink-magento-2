<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\DefaultAttributeMappingProviderInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class UpgradeData implements UpgradeDataInterface
{
    use DataHelper;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var DefaultAttributeMappingProviderInterface
     */
    private $defaultAttributeMappingsProvider;

    /**
     * Create a new Upgrade Data instance.
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        DefaultAttributeMappingProviderInterface $defaultAttributeMappingsProvider
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->defaultAttributeMappingsProvider = $defaultAttributeMappingsProvider;
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
            $this->addNewProductTypeAttributeIfNoneExists($eavSetup);
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

    /**
     * In version 1.1 and earlier, SkyLink for Magento created an attribute called "product_type". Turns out that
     * is reserved in product imports so we will create a new attribute that matches the intended code, leaving
     * the old one in place. Part of the upgrade process is a note
     */
    private function addNewProductTypeAttributeIfNoneExists(EavSetup $eavSetup)
    {
        $productType = SkyLinkAttributeCode::get('product_type');

        $intendedAttributeCode = array_get(
            $this->defaultAttributeMappingsProvider->getDefaultMappings(),
            (string) $productType
        );

        // If there's no attribute mapping defined, it's likely it's been overriden or removed through configuration
        // so we can just ignore it. It's likely that this will never occur, but let's just put it in here rather
        // than have to deal with a complaint from the extension breaking when we install it...
        if (null === $intendedAttributeCode) {
            return;
        }

        $hasExistingAttribute = (bool) $eavSetup->getAttributeId(Product::ENTITY, $intendedAttributeCode);

        // If the extension was installed at version 1.2 or later, the attribute will exist. If this is the
        // case then we can just skip recreating it. Woo!
        if (true === $hasExistingAttribute) {
            return;
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            $intendedAttributeCode,
            [
                'label' => $productType->getLabel(),
                'required' => false,
                'input' => $productType->isPredefined() ? 'select' : 'text',
                'user_defined' => true,
                'filterable' => 1,
            ]
        );

        $this->addAttributeToDefaultGroupInAllSets($eavSetup, $intendedAttributeCode, Product::ENTITY);
    }
}
