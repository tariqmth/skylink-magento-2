<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use InvalidArgumentException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Exceptions\Products\AttributeNotMappedException;
use RetailExpress\SkyLink\Exceptions\Products\AttributeOptionNotMappedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoProductMapper implements MagentoProductMapperInterface
{
    private $attributeSetRepository;

    private $attributeRepository;

    private $attributeOptionRepository;

    public function __construct(
        MagentoAttributeSetRepositoryInterface $attributeSetRepository,
        MagentoAttributeRepositoryInterface $attributeRepository,
        MagentoAttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeRepository = $attributeRepository;
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

        // @todo Set the product to be available on the main website, but what if another sales channel uses
        // the same website? How will we put it on that website? R&D time!

        // Setup pricing for product
        $magentoProduct->setPrice($skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative());
        $magentoProduct->setCustomAttribute('special_price', $skyLinkProduct->getPricingStructure()->getSpecialPrice()->toNative());

        // Use the cubic weight for the given product
        // @todo this should be configuration-based
        $magentoProduct->setWeight($skyLinkProduct->getPhysicalPackage()->getWeight()->toNative());

        // Until we extend the stock item itself, we are storing the qty on order against the product itself
        if ($skyLinkProduct->getInventoryItem()->hasQtyOnOrder()) {
            $magentoProduct->setCustomAttribute('qty_on_order', $skyLinkProduct->getInventoryItem()->getQtyOnOrder()->toNative());
        }

        // @todo map inventory, physical package and attributes
        foreach (SkyLinkAttributeCode::getConstants() as $skyLinkAttributeCodeString) {
            $skyLinkAttributeCode = SkyLinkAttributeCode::get($skyLinkAttributeCodeString);
            $skyLinkAttributeOption = $skyLinkProduct->getAttributeOption($skyLinkAttributeCode);

            // @todo deal with orphaned options?
            if (null === $skyLinkAttributeOption) {
                continue;
            }

            /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface $magentoAttribute */
            /* @var \Magento\Eav\Api\Data\AttributeOptionInterface $magentoAttributeOption */
            list($magentoAttribute, $magentoAttributeOption) = $this
                ->getMagentoAttributeAndOptionFromSkyLinkCounterparts(
                    $skyLinkAttributeCode, $skyLinkAttributeOption
                );

            // @todo Do we validate the attribute in the product's attribute set?
            $magentoProduct->setCustomAttribute(
                $magentoAttribute->getAttributeCode(),
                $magentoAttributeOption->getValue()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapMagentoProductForSalesChannelGroup(
        ProductInterface $magentoProduct,
        SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
    ) {
        $this->assertImplementationOfProductInterface($magentoProduct);

        $skyLinkProduct = $skyLinkProductInSalesChannelGroup->getSkyLinkProduct();
        $magentoWebsites = $skyLinkProductInSalesChannelGroup->getSalesChannelGroup()->getMagentoWebsites();
        $magentoStores = $skyLinkProductInSalesChannelGroup->getSalesChannelGroup()->getMagentoStores();

        // Let's make sure the product is on the given website
        $magentoProduct->setWebsiteIds(array_merge(
            $magentoProduct->getWebsiteIds(),
            array_map(function (WebsiteInterface $magentoWebsite) {
                return $magentoWebsite->getId();
            }, $magentoWebsites)
        ));

        // Now we'll update the product data for each Magento Store
        array_walk($magentoStores, function (StoreInterface $magentoStore) use ($magentoProduct, $skyLinkProduct) {
            $magentoStoreId = $magentoStore->getId();

            $magentoProduct->addAttributeUpdate(
                'name',
                (string) $skyLinkProduct->getName(),
                $magentoStoreId
            );

            $magentoProduct->addAttributeUpdate(
                'price',
                $skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative(),
                $magentoStoreId
            );

            $magentoProduct->addAttributeUpdate(
                'special_price',
                $skyLinkProduct->getPricingStructure()->getSpecialPrice()->toNative(),
                $magentoStoreId
            );
        });
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

        $magentoProduct->setSku((string) $skyLinkProduct->getSku());
        $magentoProduct->setCustomAttribute('skylink_product_id', (string) $skyLinkProduct->getId());

        $magentoProduct->setName((string) $skyLinkProduct->getName());
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

    /**
     * Get the Magento Attriubte and Option for the given SkyLink counterparts.
     *
     * @param SkyLinkAttributeCode   $skyLinkAttributeCode
     * @param SkyLinkAttributeOption $skyLinkAttributeOption
     *
     * @return [\Magento\Catalog\Api\Data\ProductAttributeInterface, \Magento\Eav\Api\Data\AttributeOptionInterface]
     *
     * @throws AttributeNotMappedException       When there is no Attribute mapping
     * @throws AttributeOptionNotMappedException When there is no Attribute Option mapping
     */
    private function getMagentoAttributeAndOptionFromSkyLinkCounterparts(
        SkyLinkAttributeCode $skyLinkAttributeCode,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        // Find the corresponding Magento attribute
        $magentoAttribute = $this
            ->attributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);

        // If we can't find a corresponding Magento Attribute, we can't continue mapping the product
        if (null === $magentoAttribute) {
            throw AttributeNotMappedException::withSkyLinkAttributeCode($skyLinkAttributeCode);
        }

        $magentoAttributeOption = $this
            ->attributeOptionRepository
            ->getMappedMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

        if (null === $magentoAttributeOption) {
            throw AttributeOptionNotMappedException::withSkyLinkAttributeOption($skyLinkAttributeOption);
        }

        return [$magentoAttribute, $magentoAttributeOption];
    }

    private function assertImplementationOfProductInterface(ProductInterface $product)
    {
        if (!$product instanceof Product) {
            throw new InvalidArgumentException(sprintf(
                'Updating a Magento Product for a Sales Channel Group requires the Product be an instanceof %s.',
                Product::class
            ));
        }
    }
}
