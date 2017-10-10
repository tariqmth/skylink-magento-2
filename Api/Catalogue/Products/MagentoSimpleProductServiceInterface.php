<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface MagentoSimpleProductServiceInterface
{
    /**
     * Create a new Magento Product based on the given SkyLink Product.
     *
     * @param SkyLinkProduct $skyLinkProduct
     *
     * @return ProductInterface
     */
    public function createMagentoProduct(SkyLinkProduct $skyLinkProduct);

    /**
     * Update the given Magento Product with the information from the SkyLink Product.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     *
     * @return ProductInterface
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct);
}
