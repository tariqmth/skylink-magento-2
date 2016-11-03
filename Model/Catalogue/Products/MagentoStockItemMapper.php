<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoStockItemMapperInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\InventoryItem as SkyLinkInventoryItem;

class MagentoStockItemMapper implements MagentoStockItemMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapStockItem(
        StockItemInterface $magentoStockItem,
        SkyLinkInventoryItem $skyLinkInventoryItem
    ) {
        $magentoStockItem->setManageStock($skyLinkInventoryItem->isManaged());
        $magentoStockItem->setQty($skyLinkInventoryItem->getQty()->toNative());
    }
}
