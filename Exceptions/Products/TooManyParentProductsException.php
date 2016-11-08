<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;

class TooManyParentProductsException extends LocalizedException
{
    public static function withChildProduct(ProductInterface $childProduct, $parentsCount)
    {
        return new self(__(
            'Product #%1 belongs to %2 parents, SkyLink is compatible with managing products belonging to one parent only.',
            $childProduct->getId(),
            $parentsCount
        ));
    }
}
