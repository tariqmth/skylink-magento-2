<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

interface MagentoAttributeRepositoryInterface
{
    /**
     * Return an array of all Magento Attributes, not grouped by their type.
     *
     * @return \Magento\Catalog\Api\Data\EavAttributeInterface[]
     */
    public function getMagentoAttributes();

    /**
     * Return an array of Magento Attributes, grouped by their type.
     *
     * [
     *   [
     *     "type" => \RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType,
     *     "attributes" => \Magento\Catalog\Api\Data\EavAttributeInterface[],
     *   ]
     * ]
     *
     * @return array
     */
    public function getMagentoAttributesByType();

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
