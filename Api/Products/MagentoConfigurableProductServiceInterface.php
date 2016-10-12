<?php

namespace RetailExpress\SkyLink\Api\Products;

use Magento\Catalog\Api\Data\ProductInterface;

interface MagentoConfigurableProductServiceInterface
{
    /**
     * Synchronises the children of the given configurable product to be the children products given.
     *
     * @param ProductInterface   $configurableProduct
     * @param ProductInterface[] $simpleProducts
     */
    public function syncChildren(ProductInterface $configurableProduct, array $simpleProducts);
}
