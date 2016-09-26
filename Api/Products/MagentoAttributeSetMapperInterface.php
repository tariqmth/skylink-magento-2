<?php

namespace RetailExpress\SkyLink\Magento2\Api\Products;

use Magento\Eav\Api\Data\AttributeSetInterface;

interface MagentoAttributeSetMapperInterface
{
    /**
     * Defines the Attribute Set used when SkyLink creates a new product in
     * Magento for the given SkyLink "product type".
     *
     * @param AttributeSetInterface $attributeSet
     * @param string                $productType
     */
    public function mapAttributeSetForProductType(
        AttributeSetInterface $attributeSet,
        $productType
    );

    /**
     * Get the Attribute Set used for the given product type. If there is no
     * mapping defined, "null" is returend.
     *
     * @param  string $productType
     *
     * @return AttributeSetInterface|null
     */
    public function getAttributeSetForProductType($productType);
}
