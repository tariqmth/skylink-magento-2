<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

interface MagentoConfigurableProductStockItemUpdaterInterface
{
    /**
     * Updates a Magento Stock Item for a Magento Configurable Product.
     *
     * @param StockItemInterface   $magentoStockItem
     */
    public function updateStockItem(StockItemInterface $magentoStockItem);
}
