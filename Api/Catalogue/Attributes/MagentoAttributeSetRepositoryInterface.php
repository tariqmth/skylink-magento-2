<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface MagentoAttributeSetRepositoryInterface
{
    /**
     * Get the Attribute Set used for the given product type. If there is no
     * mapping defined, "null" is returend.
     *
     * @param SkyLinkAttributeOption $skyLinkProductType
     *
     * @return \Magento\Eav\Api\Data\AttributeSetInterface|null
     */
    public function getAttributeSetForProductType(SkyLinkAttributeOption $productType);
}
