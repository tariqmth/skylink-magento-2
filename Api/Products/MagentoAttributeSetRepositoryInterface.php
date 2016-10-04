<?php

namespace RetailExpress\SkyLink\Api\Products;

use RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface MagentoAttributeSetRepositoryInterface
{
    /**
     * Get the Attribute Set used for the given product type. If there is no
     * mapping defined, "null" is returend.
     *
     * @param SkyLinkAttributeOption $productType
     *
     * @return \Magento\Eav\Api\Data\AttributeSetInterface|null
     */
    public function getAttributeSetForProductType(SkyLinkAttributeOption $productType);
}
