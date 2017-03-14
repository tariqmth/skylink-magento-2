<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Products\CompositeProduct as CompositeSkyLinkProduct;

interface MagentoSyncCompositeProductRerunManagerInterface
{
    /**
     * Returns whether a sync can proceed for the given SkyLink Composite Product, based
     * on whatever logic determines how often a potential rerun can occur.
     *
     * @param CompositeSkyLinkProduct $skyLinkCompositeProduct
     *
     * @return bool
     */
    public function canProceedWithSync(CompositeSkyLinkProduct $skyLinkCompositeProduct);

    /**
     * Advise the Manager that a Composite SkyLink Product is syncing.
     *
     * @param CompositeSkyLinkProduct $skyLinkCompositeProduct
     */
    public function isSyncing(CompositeSkyLinkProduct $skyLinkCompositeProduct);
}
