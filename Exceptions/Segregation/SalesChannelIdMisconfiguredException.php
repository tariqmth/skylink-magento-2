<?php

namespace RetailExpress\SkyLink\Exceptions\Segregation;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\WebsiteInterface;

class SalesChannelIdMisconfiguredException extends LocalizedException
{
    public static function forWebsiteWithConfigValue(WebsiteInterface $website, $configValue)
    {
        return new self(__(
            'Website "%1" has an invalid Sales Channel ID "%2" configured. It must be numeric.',
            $website->getCode(),
            $configValue
        ));
    }
}
