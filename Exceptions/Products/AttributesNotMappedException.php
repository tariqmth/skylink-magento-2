<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class AttributeNotMappedException extends LocalizedException
{
    public static function withSkyLinkAttributeCode(SkyLinkAttributeCode $skyLinkAttributeCode)
    {
        return new self(__(
            'There was no Magento Attribute mapped for SkyLink Attribute %1, please re-sync.',
            $skyLinkAttributeCode
        ));
    }
}
