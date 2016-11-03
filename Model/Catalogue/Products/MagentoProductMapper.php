<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
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
        }

        // Setup pricing for product
        $magentoProduct->setPrice($skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative());
        $magentoProduct->setCustomAttribute('special_price', $skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative());

        // Use the cubic weight for the given product
        // @todo this should be configuration-based
        $magentoProduct->setWeight($skyLinkProduct->getPhysicalPackage()->getCubicWeight()->toNative());

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
}
