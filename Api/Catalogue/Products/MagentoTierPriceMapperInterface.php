<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface MagentoTierPriceMapperInterface
{
    /**
     * Maps tier prices on the given Magento Product for the given SkyLink Product.
     */
    public function map(
        ProductInterface $magentoProduct,
        SkyLinkProduct $skyLinkProduct,
        WebsiteInterface $magentoWebsite = null
    );
}
