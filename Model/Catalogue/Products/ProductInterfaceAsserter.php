<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use InvalidArgumentException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

trait ProductInterfaceAsserter
{
    private function assertImplementationOfProductInterface(ProductInterface $product)
    {
        if (!$product instanceof Product) {
            throw new InvalidArgumentException(sprintf(
                'Updating a Magento Product for a Sales Channel Group requires the Product be an instance of %s.',
                Product::class
            ));
        }
    }
}
