<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\InventoryItem as SkyLinkInventoryItem;

interface MagentoSimpleProductStockItemMapperInterface
{
    /**
     * Maps a SkyLink Inventory Item to a Magento Stock Item.
     *
     * @param StockItemInterface   $magentoStockItem
     * @param SkyLinkInventoryItem $skyLinkPhysicalPackage
     */
    public function mapStockItem(
        StockItemInterface $magentoStockItem,
        SkyLinkInventoryItem $skyLinkInventoryItem
    );
}
