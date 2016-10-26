<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoProductMapper implements MagentoProductMapperInterface
{
    private $attributeSetRepository;

    public function __construct(
        MagentoAttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
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
            $this->attributeSetRepository->getAttributeSetForProductType($skyLinkProduct->getProductType())->getId()
        );

        $magentoProduct->setSku((string) $skyLinkProduct->getSku());
        $magentoProduct->setCustomAttribute('skylink_product_id', (string) $skyLinkProduct->getId());

        $magentoProduct->setName((string) $skyLinkProduct->getName());

        $magentoProduct->setPrice($skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative());
        $magentoProduct->setCustomAttribute('special_price', $skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative());
    }
}
