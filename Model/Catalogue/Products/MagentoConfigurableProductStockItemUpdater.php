<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductStockItemUpdaterInterface;

class MagentoConfigurableProductStockItemUpdater implements MagentoConfigurableProductStockItemUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function updateStockItem(StockItemInterface $magentoStockItem)
    {
        $magentoStockItem->setIsInStock(true);
    }
}
