<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeTypeManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Exceptions\Products\AttributeNotMappedException;
use RetailExpress\SkyLink\Exceptions\Products\AttributeOptionNotMappedException;
use RetailExpress\SkyLink\Model\Catalogue\SyncStrategy;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoProductMapper implements MagentoProductMapperInterface
{
    use ProductInterfaceAsserter;

    private $productConfig;

    private $attributeSetRepository;

    private $attributeRepository;

    private $attributeTypeManager;

    private $attributeOptionRepository;

    public function __construct(
        ConfigInterface $productConfig,
        MagentoAttributeSetRepositoryInterface $attributeSetRepository,
        MagentoAttributeRepositoryInterface $attributeRepository,
        MagentoAttributeTypeManagerInterface $attributeTypeManager,
        MagentoAttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        $this->productConfig = $productConfig;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeTypeManager = $attributeTypeManager;
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function mapMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        // New products require a little more mapping
        if (!$magentoProduct->getId()) {
            $this->mapNewMagentoProduct($magentoProduct, $skyLinkProduct);
        } else {
            $this->overrideVisibilityForExistingProduct($magentoProduct);
        }

        $magentoProduct->setSku((string) $skyLinkProduct->getSku());

        $magentoProduct->unsetData('manufacturer_sku');
        $magentoProduct->setData('manufacturer_sku', (string) $skyLinkProduct->getManufacturerSku());

        $this->mapName($magentoProduct, $skyLinkProduct);

        $this->mapPrices($magentoProduct, $skyLinkProduct);

        // Use the cubic weight for the given product
        // @todo this should be configuration-based
        $magentoProduct->setWeight($skyLinkProduct->getPhysicalPackage()->getWeight()->toNative());

        // Until we extend the stock item itself, we are storing the qty on order against the product itself
        $magentoProduct->unsetData('qty_on_order');
        if ($skyLinkProduct->getInventoryItem()->hasQtyOnOrder()) {
            $magentoProduct->setCustomAttribute('qty_on_order', $skyLinkProduct->getInventoryItem()->getQtyOnOrder()->toNative());
        } else {
            $magentoProduct->setCustomAttribute('qty_on_order', 0);
        }

        // All other attributes
        $this->mapAttributes($magentoProduct, $skyLinkProduct);
    }

    public function mapMagentoProductForWebsite(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $this->mapPrices($magentoProduct, $skyLinkProduct);
    }

    /**
     * Maps a new Magento Product based on the given SkyLink Product.
     *
     * @param ProductInterface $product
     * @param SkyLinkProduct   $skyLinkProduct
     */
    private function mapNewMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        // Set the attribute set according to what is mapped for the given product type
        $magentoProduct->setAttributeSetId(
            $this
                ->attributeSetRepository
                ->getAttributeSetForProductType($skyLinkProduct->getProductType())->getId()
        );

        $magentoProduct->unsetData('skylink_product_id');
        $magentoProduct->setCustomAttribute('skylink_product_id', (string) $skyLinkProduct->getId());
    }

    /**
     * Looks at an existing Magento Product's visibility and if it's been marked as invisible,
     * will make it as visible in both catalogue and search. Product who have been adjsuted
     * to be visible in only catalogue or search will not be modified. This is to account
     * for products that were in a configurable product that no longer are, they must
     * become visible.
     *
     * @todo Because this is called during the configurable product syncing process, all child
     *       products will be set to visible, and then back in MagentoConfigurableProductLinkManagement
     *       set to invisible. See if we can't streamline this without passing an "adjustVisibility"
     *       paramter through a bunch of classes...
     *
     * @param ProductInterface $magentoProduct
     */
    private function overrideVisibilityForExistingProduct(ProductInterface $magentoProduct)
    {
        $currentVisibility = $magentoProduct->getVisibility();

        if (Visibility::VISIBILITY_NOT_VISIBLE === $currentVisibility) {
            $magentoProduct->setVisibility(Visibility::VISIBILITY_BOTH);
        }
    }

    private function mapName(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $syncStrategy = $this->productConfig->getNameSyncStrategy();

        // If the product is new or we always sync the name, we'll sync it now
        if (!$magentoProduct->getId() || $syncStrategy->sameValueAs(SyncStrategy::get('always'))) {
            $magentoProduct->setName((string) $skyLinkProduct->getName());
        }
    }

    private function mapPrices(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        // Setup pricing for product
        $magentoProduct->setPrice($skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative());
        $magentoProduct->unsetData('special_price');
        $magentoProduct->setCustomAttribute('special_price', $skyLinkProduct->getPricingStructure()->getSpecialPrice()->toNative());
    }

    private function mapAttributes(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        foreach (SkyLinkAttributeCode::getConstants() as $skyLinkAttributeCodeString) {
            $skyLinkAttributeCode = SkyLinkAttributeCode::get($skyLinkAttributeCodeString);

            /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface $magentoAttribute */
            $magentoAttribute = $this->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);

            /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption $skyLinkAttributeOption */
            $skyLinkAttributeOption = $skyLinkProduct->getAttributeOption($skyLinkAttributeCode);

            // If there's no value for the SkyLink Attribute Option, we'll wipe the custom attribute value
            if (null === $skyLinkAttributeOption) {
                $magentoProduct->unsetData($magentoAttribute->getAttributeCode());
                $magentoProduct->setCustomAttribute($magentoAttribute->getAttributeCode(), null);
                continue;
            }

            /* @var \RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType $magentoAttributeType */
            $magentoAttributeType = $this->attributeTypeManager->getType($magentoAttribute);

            // If we use options, we'll grab the mapped option
            if ($magentoAttributeType->usesOptions()) {
                $magentoAttributeValue = $this
                    ->getMagentoAttributeOptionFromSkyLinkAttributeOption($skyLinkAttributeOption)
                    ->getValue();
            // Otherweise, we'll use the label for the SkyLink Attribute Option
            } else {
                $magentoAttributeValue = $skyLinkAttributeOption->getLabel()->toNative();
            }

            // Now we'll set the custom attribute value
            $magentoProduct->unsetData($magentoAttribute->getAttributeCode());
            $magentoProduct->setCustomAttribute(
                $magentoAttribute->getAttributeCode(),
                $magentoAttributeValue
            );
        }
    }

    /**
     * Get the Magento Attribute for the given SkyLink Attribute Code.
     *
     * @param SkyLinkAttributeCode   $skyLinkAttributeCode
     * @param SkyLinkAttributeOption $skyLinkAttributeOption
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     *
     * @throws AttributeNotMappedException       When there is no Attribute mapping
     */
    private function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skyLinkAttributeCode)
    {
        /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface|null $magentoAttribute */
        $magentoAttribute = $this
            ->attributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);

        // If we can't find a corresponding Magento Attribute, we can't continue mapping the product
        if (null === $magentoAttribute) {
            throw AttributeNotMappedException::withSkyLinkAttributeCode($skyLinkAttributeCode);
        }

        return $magentoAttribute;
    }

    /**
     * Get the Magento Attribute Option for the given SkyLink Attribute Option.
     *
     * @param SkyLinkAttributeOption $skyLinkAttributeOption
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface
     *
     * @throws AttributeOptionNotMappedException When there is no Attribute Option mapping
     */
    private function getMagentoAttributeOptionFromSkyLinkAttributeOption(
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $magentoAttributeOption = $this
            ->attributeOptionRepository
            ->getMappedMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

        if (null === $magentoAttributeOption) {
            throw AttributeOptionNotMappedException::withSkyLinkAttributeOption($skyLinkAttributeOption);
        }

        return $magentoAttributeOption;
    }


}
