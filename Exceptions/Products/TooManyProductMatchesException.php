<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class TooManyProductMatchesException extends LocalizedException
{
    /**
     * Create an new Exception with the given SkyLink Product ID and a numerical number of matches.
     *
     * @param SkyLinkProductId $skyLinkProductId
     * @param int               $matches
     *
     * @return TooManyProductMatchesException
     *
     * @codeCoverageIgnore
     */
    public static function withSkyLinkProductId(SkyLinkProductId $skyLinkProductId, $matches)
    {
        return new self(__(
            'There were %1 matches for products using "%2" as their SkyLink Product ID.',
            $matches,
            $skyLinkProductId
        ));
    }
}
