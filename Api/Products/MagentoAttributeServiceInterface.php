<?php

namespace RetailExpress\SkyLink\Api\Products;

use Magento\Eav\Api\Data\AttributeInterface;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

interface MagentoAttributeServiceInterface
{
    /**
     * Defines the Attribute used when SkyLink synchronises a Product.
     *
     * @param AttributeInterface   $magentoAttribute
     * @param SkyLinkAttributeCode $skylinkAttributeCode
     */
    public function mapAttributeForProductType(
        AttributeInterface $magentoAttribute,
        SkyLinkAttributeCode $skylinkAttributeCode
    );
}
