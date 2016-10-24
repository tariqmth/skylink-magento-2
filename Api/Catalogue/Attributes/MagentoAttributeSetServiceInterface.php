<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use Magento\Eav\Api\Data\AttributeSetInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface MagentoAttributeSetServiceInterface
{
    /**
     * Defines the Attribute Set used when SkyLink creates a new product in
     * Magento for the given SkyLink "product type".
     *
     * @param AttributeSetInterface  $magentoAttributeSet
     * @param SkyLinkAttributeOption $skyLinkProductType
     */
    public function mapAttributeSetForProductType(
        AttributeSetInterface $magentoAttributeSet,
        SkyLinkAttributeOption $skyLinkProductType
    );
}
