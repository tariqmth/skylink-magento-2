<?php

namespace RetailExpress\SkyLink\Magento2\Api\Products;

use Magento\Eav\Api\Data\AttributeInterface;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

interface MagentoAttributeRepositoryInterface
{
    /**
     * Get the Attribute used for the given SkyLink Attribute Code. If there is no
     * mapping defined, "null" is returend.
     *
     * @param SkyLinkAttributeCode $skylinkAttributeCode
     *
     * @return AttributeInterface|null
     */
    public function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode);
}
