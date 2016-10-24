<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;

interface SkyLinkProductToMagentoProductSyncerInterface
{
    /**
     * Determine if the syncer accepts the given product to sync.
     *
     * @param Product $product
     *
     * @return bool
     */
    public function accepts(Product $product);

    /**
     * Perform the actual sync of the given product.
     *
     * @param Product $product
     */
    public function sync(Product $product);
}
