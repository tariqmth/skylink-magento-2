<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;

class TooManyCustomerMatchesException extends LocalizedException
{
    /**
     * Create an new Exception with the given Retail Express Customer ID and a numerical number of matches.
     *
     * @param SkyLinkCustomerId $retailExpressCustomerId
     * @param int               $matches
     *
     * @return TooManyCustomerMatchesException
     */
    public static function withRetailExpressCustomerId(SkyLinkCustomerId $retailExpressCustomerId, $matches)
    {
        return new self(__(
            'There were %1 matches for customers using "%2" as their Retail Express Customer ID.',
            $matches,
            $retailExpressCustomerId
        ));
    }
}
