<?php

namespace RetailExpress\SkyLink\Exceptions\Customers;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

class CustomerGroupNotSyncedException extends LocalizedException
{
    /**
     * @param SkyLinkPriceGroupKey $skyLinkPriceGroupKey
     *
     * @return CustomerGroupNotSyncedException
     *
     * @codeCoverageIgnore
     */
    public static function withSkyLinkPriceGroupKey(SkyLinkPriceGroupKey $skyLinkPriceGroupKey)
    {
        return new self(__(
            'The SkyLink "%1" Price Group #%2 has not yet been synced to a Magento Customer Group',
            $skyLinkPriceGroupKey->getType(),
            $skyLinkPriceGroupKey->getId()
        ));
    }
}
