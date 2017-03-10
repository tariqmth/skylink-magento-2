<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface MagentoProductMapperInterface
{
    /**
     * Map a Magento Product based on the given SkyLink Product.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     */
    public function mapMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct);

    /**
     * Map a Magento Product based on what can be mapped/overridden in the current set Store.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     */
    public function mapMagentoProductForWebsite(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct);
}
