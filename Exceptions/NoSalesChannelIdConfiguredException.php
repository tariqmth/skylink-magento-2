<?php

namespace RetailExpress\SkyLink\Exceptions;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\WebsiteInterface;

class NoSalesChannelIdConfiguredException extends LocalizedException
{
    public static function forGlobalScope()
    {
        return new self(__('A global Sales Channel ID has not been configured.'));
    }
}
