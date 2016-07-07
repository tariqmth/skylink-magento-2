<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;

class TooManyCustomerMatchesException extends LocalizedException
{
    public static function withRetailExpressCustomerId(SkyLinkCustomerId $retailExpressCustomerId, $matches)
    {
        return new self(__(
            'There were %1 matches for customers using "%2" as their Retail Express Customer ID.',
            $matches,
            $retailExpressCustomerId
        ));
    }
}
