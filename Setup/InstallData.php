<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceIndexer;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Cache\Type\Config as ConfigCacheType;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use \Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\DefaultAttributeMappingProviderInterface;
use RetailExpress\SkyLink\Model\Eav\Entity\Attribute\Source\PickupGroup as PickupGroupSourceModel;
use RetailExpress\SkyLink\Model\Pickup\PickupGroup;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class InstallData implements InstallDataInterface
{
    use DataHelper;

    private $eavSetupFactory;

    private $eavConfig;

    private $scopeConfig;

    private $indexerRegistry;

    private $cacheTypeList;

    private $defaultAttributeMappingsProvider;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        EavConfig $eavConfig,
        MutableScopeConfigInterface $scopeConfig,
        IndexerRegistry $indexerRegistry,
        CacheTypeListInterface $cacheTypeList,
        DefaultAttributeMappingProviderInterface $defaultAttributeMappingsProvider
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->scopeConfig = $scopeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->cacheTypeList = $cacheTypeList;
        $this->defaultAttributeMappingsProvider = $defaultAttributeMappingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /* @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $this->addSkyLinkCustomerIdToCustomers($eavSetup);
        $this->addSkyLinkProductIdsToProducts($eavSetup);
        $this->addManufacturerSkuToProducts($eavSetup);
        $this->addSkyLinkAttributeCodesToProducts($eavSetup);
        $this->addManufacturerToAttributeSets($eavSetup);
        $this->addPickupGroupToProducts($eavSetup);
        $this->disableConfiguredMultishipping($setup);
        $this->addQtyOnOrderToProducts($eavSetup);
        $this->makeProductPricingConfiguredPerWebsite($setup);
        $this->makeCustomerSharingGlobal($setup);

        $setup->endSetup();
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
                'apply_to' => 'simple',
            ]
        );

        $this->addAttributeToDefaultGroupInAllSets($eavSetup, $attributeCode, Product::ENTITY);
    }

    private function addManufacturerSkuToProducts(EavSetup $eavSetup)
    {
        $attributeCode = 'manufacturer_sku';

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'label' => 'Manufacturer SKU',
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
            $magentoAttributeCode = $this->getMagentoAttributeCode($skyLinkAttributeCode);

            $hasExistingAttribute = (bool) $eavSetup
                ->getAttributeId(Product::ENTITY, $magentoAttributeCode);

            if (false === $hasExistingAttribute) {

                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $magentoAttributeCode,
                    [
                        'label' => $skyLinkAttributeCode->getLabel(),
                        'required' => false,
                        'input' => $skyLinkAttributeCode->isPredefined() ? 'select' : 'text',
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

    private function disableConfiguredMultishipping(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => 'multishipping/options/checkout_multiple',
            ]
        );

        $this->cacheTypeList->invalidate(ConfigCacheType::TYPE_IDENTIFIER);
    }

    private function addQtyOnOrderToProducts(EavSetup $eavSetup)
    {
        $attributeCode = 'qty_on_order';

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'label' => 'Qty On Order',
                'required' => false,
                'user_defined' => true,
            ]
        );

        $this->addAttributeToDefaultGroupInAllSets($eavSetup, $attributeCode, Product::ENTITY);
    }

    private function makeProductPricingConfiguredPerWebsite(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => 'catalog/price/scope',
            ]
        );

        $this->cacheTypeList->invalidate(ConfigCacheType::TYPE_IDENTIFIER);
        $this->indexerRegistry->get(ProductPriceIndexer::INDEXER_ID)->invalidate();
    }

    private function makeCustomerSharingGlobal(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => 'customer/account_share/scope',
            ]
        );

        $this->cacheTypeList->invalidate(ConfigCacheType::TYPE_IDENTIFIER);
    }

    /**
     * Gets the Magento Attribute Code to use for the  given SkyLink Attribute Code.
     *
     * @return string
     */
    private function getMagentoAttributeCode(SkyLinkAttributeCode $skyLinkAttributeCode)
    {
        $defaultAttributeMappings = $this->defaultAttributeMappingsProvider->getDefaultMappings();

        if (array_key_exists($skyLinkAttributeCode->getValue(), $defaultAttributeMappings)) {
            return $defaultAttributeMappings[$skyLinkAttributeCode->getValue()];
        }

        return $skyLinkAttributeCode->getValue();
    }
}
