<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class AttributeOptionNotMappedException extends LocalizedException
{
    public static function withSkyLinkAttributeOption(SkyLinkAttributeOption $skyLinkAttributeOption)
    {
        return new self(__(
            'There was no Magento Attribute Option mapped for SkyLink Attribute "%1" Option "%2", please re-sync.',
            $skyLinkAttributeOption->getAttribute()->getCode(),
            $skyLinkAttributeOption
        ));
    }
}
