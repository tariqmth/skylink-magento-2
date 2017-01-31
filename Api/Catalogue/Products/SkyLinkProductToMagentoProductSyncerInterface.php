<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface SkyLinkProductToMagentoProductSyncerInterface
{
    /**
     * Get the name of the syncer (used for debugging purposes).
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if the syncer accepts the given SkyLink Product to sync.
     *
     * @param SkyLinkProduct $skyLinkProduct
     *
     * @return bool
     */
    public function accepts(SkyLinkProduct $skyLinkProduct);

    /**
     * Perform the actual sync of the given SkyLink Product.
     *
     * @param SkyLinkProduct                                                                                  $skyLinkProduct                     The SkyLink product being synced
     * @param \RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface[] $skyLinkProductInSalesChannelGroups
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function sync(SkyLinkProduct $skyLinkProduct, array $skyLinkProductInSalesChannelGroups);
}
