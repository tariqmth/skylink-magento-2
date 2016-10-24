<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;

class SkyLinkSimpleProductToMagentoSimpleProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    /**
     * {@inheritdoc}
     */
    public function accepts(Product $product)
    {
        return $product instanceof SimpleProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(Product $matrix)
    {
        //
    }
}
