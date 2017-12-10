<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;

class MissingAttributeValueException extends LocalizedException
{
    public static function newInstance($skyLinkProductId, $skyLinkAttributeCode)
    {
        return new self(__(
            'Could not sync configurable product because Skylink product "%1" was missing a value for attribute "%2".'
            . ' This could also be caused if %2 is not enabled in the Magento attribute set.',
            $skyLinkProductId,
            $skyLinkAttributeCode
        ));
    }
}