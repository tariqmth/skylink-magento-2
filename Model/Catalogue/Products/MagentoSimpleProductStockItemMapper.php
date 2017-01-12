<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductStockItemMapperInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\InventoryItem as SkyLinkInventoryItem;

class MagentoSimpleProductStockItemMapper implements MagentoSimpleProductStockItemMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapStockItem(
        StockItemInterface $magentoStockItem,
        SkyLinkInventoryItem $skyLinkInventoryItem
    ) {
        $magentoStockItem->setManageStock($skyLinkInventoryItem->isManaged());

        $nativeQty = $skyLinkInventoryItem->getQty()->toNative();
        $magentoStockItem->setIsInStock($nativeQty > 0);
        $magentoStockItem->setQty($nativeQty >= 0 ? $nativeQty : 0); // Magento doesn't allow qty below 0
    }
}
