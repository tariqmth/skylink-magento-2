<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MagentoOrderItemNotLinkedToSkyLinkProductException extends LocalizedException
{
    public static function withMagentoProductId($magentoProductId)
    {
        return new self(__(
            'Magento Product #%1 is not linked to a SkyLink Product, cannot use in order.',
            $magentoProductId
        ));
    }
}
