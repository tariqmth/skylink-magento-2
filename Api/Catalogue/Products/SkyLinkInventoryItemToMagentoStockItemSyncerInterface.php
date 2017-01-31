<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;

interface SkyLinkInventoryItemToMagentoStockItemSyncerInterface
{
    /**
     * Get the name of the syncer (used for debugging purposes).
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if the syncer accepts the given Magento Product to sync.
     *
     * @param ProductInterface $magentoProduct
     *
     * @return bool
     */
    public function accepts(ProductInterface $magentoProduct);

    /**
     * Perform the actual sync of the given SkyLink Product.
     *
     * @param ProductInterface $magentoProduct
     */
    public function sync(ProductInterface $magentoProduct);
}
