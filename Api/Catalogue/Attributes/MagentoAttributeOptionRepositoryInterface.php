<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface MagentoAttributeOptionRepositoryInterface
{
    /**
     * Get the Magento Attribute Option associated with the given SkyLink Attribute Option.
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface|null
     */
    public function getMappedMagentoAttributeOptionForSkyLinkAttributeOption(
        SkyLinkAttributeOption $skyLinkAttributeOption
    );

    /**
     * Get a pottible Magento Attribute Option for the given SkyLink Attribute Option.
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface|null
     */
    public function getPossibleMagentoAttributeOptionForSkyLinkAttributeOption(
        SkyLinkAttributeOption $skyLinkAttributeOption
    );
}
