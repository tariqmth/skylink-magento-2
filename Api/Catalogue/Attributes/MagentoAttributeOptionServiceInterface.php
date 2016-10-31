<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
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
     * Create and add a Magento Attribute Option to suffice the given SkyLink
     * Attribute Option, returning the Magento Attribute Option aftewards.
     *
     * @param ProductAttributeInterface $magentoAttribute
     * @param SkyLinkAttributeOption    $skyLinkAttributeOption
     *
     * @return AttributeOptionInterface
     */
    public function createMagentoAttributeOptionForSkyLinkAttributeOption(
        ProductAttributeInterface $magentoAttribute,
        SkyLinkAttributeOption $skyLinkAttributeOption
    );
}
