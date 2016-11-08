<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;

interface UrlKeyGeneratorInterface
{
    /**
     * Generates a unique URL key for the given Magento product.
     *
     * @param ProductInterface $magentoProduct
     *
     * @return string
     */
    public function generateUniqueUrlKeyForMagentoProduct(ProductInterface $magentoProduct);
}
