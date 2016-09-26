<?php

namespace RetailExpress\SkyLink\Magento2\Api\Products;

use Magento\Eav\Api\Data\AttributeInterface;

interface MagentoAttributeMapperInterface
{
    /**
     * Defines the Attribute used when SkyLink synchronises a Product.
     *
     * @param AttributeInterface $magentoAttribute
     * @param string             $skylinkAttributeCode
     */
    public function mapAttributeForProductType(
        AttributeInterface $magentoAttribute,
        $skylinkAttributeCode
    );

    /**
     * Get the Attribute used for the given SkyLink Attribute Code. If there is no
     * mapping defined, "null" is returend.
     *
     * @param  string $skylinkAttributeCode
     *
     * @return AttributeInterface|null
     */
    public function getAttributeForProductType($skylinkAttributeCode);
}
