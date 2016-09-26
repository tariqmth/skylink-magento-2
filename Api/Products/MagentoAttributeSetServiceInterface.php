<?php

namespace RetailExpress\SkyLink\Magento2\Api\Products;

use Magento\Eav\Api\Data\AttributeSetInterface;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface MagentoAttributeSetServiceInterface
{
    /**
     * Defines the Attribute Set used when SkyLink creates a new product in
     * Magento for the given SkyLink "product type".
     *
     * @param AttributeSetInterface  $attributeSet
     * @param SkyLinkAttributeOption $productType
     */
    public function mapAttributeSetForProductType(
        AttributeSetInterface $attributeSet,
        SkyLinkAttributeOption $productType
    );
}
