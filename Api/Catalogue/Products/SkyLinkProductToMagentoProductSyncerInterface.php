<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;

interface SkyLinkProductToMagentoProductSyncerInterface
{
    /**
     * Get the name of the syncer (used for debugging purposes)
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if the syncer accepts the given SkyLink Product to sync.
     *
     * @param Product $skyLinkProduct
     *
     * @return bool
     */
    public function accepts(Product $skyLinkProduct);

    /**
     * Determine if the syncer supports syncing stock levels only.
     *
     * @return bool
     */
    public function canSyncSkyLinkInventoryItemToMagentoStockItem();

    /**
     * Perform the actual sync of the given SkyLink Product.
     *
     * @param Product $skyLinkProduct
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function sync(Product $skyLinkProduct);

    /**
     * Perform a lightweight sync of the product, updating the Magento Stock Item using the SkyLink Inventory Item.
     *
     * @param Product $skyLinkProduct
     */
    public function syncSkyLinkInventoryItemToMagentoStockItem(Product $skyLinkProduct);
}
