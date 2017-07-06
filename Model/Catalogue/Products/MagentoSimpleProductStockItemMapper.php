<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductStockItemMapperInterface;
use RetailExpress\SkyLink\Model\Catalogue\Products\QuantityCalculation;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\InventoryItem as SkyLinkInventoryItem;

class MagentoSimpleProductStockItemMapper implements MagentoSimpleProductStockItemMapperInterface
{
    private $config;

    /**
     * Create a new Simple Product Stock Item Mapper
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function mapStockItem(
        StockItemInterface $magentoStockItem,
        SkyLinkInventoryItem $skyLinkInventoryItem
    ) {
        $magentoStockItem->setUseConfigManageStock(false); // Never use config, we'll track it
        $magentoStockItem->setManageStock($skyLinkInventoryItem->isManaged());

        $quantity = $this->determineQuantity($skyLinkInventoryItem);

        $magentoStockItem->setIsInStock($quantity > 0);
        $magentoStockItem->setQty($quantity >= 0 ? $quantity : 0); // Magento doesn't allow qty below 0
    }

    /**
     * Determine the quanitty for Magento based upon the configured Quantity Calculation.
     *
     * @return SkyLinkInventoryItem $skyLinkInventoryItem
     *
     * @return float
     */
    private function determineQuantity(SkyLinkInventoryItem $skyLinkInventoryItem)
    {
        $quantity = $skyLinkInventoryItem->getQtyAvailable()->toNative();

        if ($this->addOnOrderToQuantity() && $skyLinkInventoryItem->hasQtyOnOrder()) {
            $quantity += $skyLinkInventoryItem->getQtyOnOrder()->toNative();
        }

        return $quantity;
    }

    /**
     * @return bool
     */
    private function addOnOrderToQuantity()
    {
        return $this->config->getQuantityCalculation()->sameValueAs(QuantityCalculation::get('available_on_order'));
    }
}
