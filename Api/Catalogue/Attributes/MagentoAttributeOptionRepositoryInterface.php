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
    public function getMagentoAttributeOptionForSkyLinkAttributeOption(SkyLinkAttributeOption $skyLinkAttributeOption);
}
