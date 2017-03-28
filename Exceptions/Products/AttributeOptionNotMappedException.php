<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class AttributeOptionNotMappedException extends LocalizedException
{
    private $skyLinkAttributeOption;

    public static function withSkyLinkAttributeOption(SkyLinkAttributeOption $skyLinkAttributeOption)
    {
        $exception = new self(__(
            'There was no Magento Attribute Option mapped for SkyLink Attribute "%1" Option "%2", please re-sync.',
            $skyLinkAttributeOption->getAttribute()->getCode(),
            $skyLinkAttributeOption
        ));

        $exception->skyLinkAttributeOption = $skyLinkAttributeOption;

        return $exception;
    }

    public function getSkyLinkAttributeOption()
    {
        return clone $this->skyLinkAttributeOption;
    }
}
