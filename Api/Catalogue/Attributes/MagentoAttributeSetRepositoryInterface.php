<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface MagentoAttributeSetRepositoryInterface
{
    /**
     * Get the Attribute Set used for the given product type. If there is no
     * mapping defined, the default attribute set ID for products is returned.
     *
     * @param SkyLinkAttributeOption $skyLinkProductType
     *
     * @return \Magento\Eav\Api\Data\AttributeSetInterface
     */
    public function getAttributeSetForProductType(SkyLinkAttributeOption $productType);
}
