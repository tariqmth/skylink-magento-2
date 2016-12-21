<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class SkyLinkProductDoesNotExistException extends LocalizedException
{
    public static function withSkyLinkProductId(SkyLinkProductId $skyLinkProductId)
    {
        return new self(__('SkyLink Product #%1 does not exist.', $skyLinkProductId));
    }
}
