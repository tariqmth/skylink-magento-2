<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface MagentoAttributeOptionServiceInterface
{
    /**
     * Defines the Magento Attribute Option that represents the given
     * SkyLink Attribute Option.
     *
     * @param AttributeOptionInterface $magentoAttributeOption
     * @param SkyLinkAttributeOption   $skyLinkAttributeOption
     */
    public function mapMagentoAttributeOptionForSkyLinkAttributeOption(
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    );

    /**
     * Removes the definition of the Magento Attribute Option
     * that represents the given SkyLink Attribute Option.
     *
     * @param AttributeOptionInterface $magentoAttributeOption
     * @param SkyLinkAttributeOption   $skyLinkAttributeOption
     */
    public function forgetMagentoAttributeOptionForSkyLinkAttributeOption(
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    );
}
