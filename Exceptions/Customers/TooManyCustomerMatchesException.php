<?php

namespace RetailExpress\SkyLink\Exceptions\Customers;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;

class TooManyCustomerMatchesException extends LocalizedException
{
    /**
     * Create an new Exception with the given SkyLink Customer ID and a numerical number of matches.
     *
     * @param SkyLinkCustomerId $skyLinkCustomerId
     * @param int               $matches
     *
     * @return TooManyCustomerMatchesException
     *
     * @codeCoverageIgnore
     */
    public static function withSkyLinkCustomerId(SkyLinkCustomerId $skyLinkCustomerId, $matches)
    {
        return new self(__(
            'There were %1 matches for customers using "%2" as their SkyLink Customer ID.',
            $matches,
            $skyLinkCustomerId
        ));
    }
}
