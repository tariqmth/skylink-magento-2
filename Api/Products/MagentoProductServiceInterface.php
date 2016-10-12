<?php

namespace RetailExpress\SkyLink\Api\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Catalogue\Products\Product as SkyLinkProduct;

interface MagentoProductService
{
    /**
     * Create a new Magento Product based on the given SkyLink Product
     *
     * @param SkyLinkProduct $skyLinkProduct
     */
    public function createMagentoProduct(SkyLinkProduct $skyLinkProduct);

    /**
     * Update the given Magento Product with the information from the SkyLink Product.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct);
}
