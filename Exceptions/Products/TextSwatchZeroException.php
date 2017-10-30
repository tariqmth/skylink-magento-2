<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class TextSwatchZeroException extends LocalizedException
{
    public static function withSkyLinkAttributeOption(SkyLinkAttributeOption $skyLinkAttributeOption)
    {
        $exception = new self(__(
            'SkyLink attribute "%1" with label "%2" cannot be synced to a Magento text swatch. ' .
            'Please use a value other than numerical zero (0) or map to a drop down attribute.',
            $skyLinkAttributeOption->getAttribute()->getCode(),
            $skyLinkAttributeOption->getLabel()
        ));

        return $exception;
    }
}
