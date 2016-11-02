<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

interface MagentoAttributeRepositoryInterface
{
    /**
     * Get the Attribute used for the given SkyLink Attribute Code. If there is no
     * mapping defined, "null" is returend.
     *
     * @param SkyLinkAttributeCode $skylinkAttributeCode
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface|null
     */
    public function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode);

    /**
     * Get the default Attribute used fro the given SkyLink Attribute Code. Since we create attributes
     * for all possible SkyLink Attribute Codes, we will always have the ability to something.
     *
     * @param SkyLinkAttributeCode $skyLinkAttributeCode
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getDefaultMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode);
}
