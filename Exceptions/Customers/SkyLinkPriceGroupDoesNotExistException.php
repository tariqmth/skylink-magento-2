<?php

namespace RetailExpress\SkyLink\Exceptions\Customers;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

class SkyLinkPriceGroupDoesNotExistException extends LocalizedException
{
    /**
     * @param SkyLinkPriceGroupKey $skyLinkPriceGroupKey
     *
     * @return SkyLinkPriceGroupDoesNotExistException
     *
     * @codeCoverageIgnore
     */
    public static function withSkyLinkPriceGroupKey(SkyLinkPriceGroupKey $skyLinkPriceGroupKey)
    {
        return new self(__(
            'The SkyLink "%1" Price Group #%2 does not exist.',
            $skyLinkPriceGroupKey->getType(),
            $skyLinkPriceGroupKey->getId()
        ));
    }
}
