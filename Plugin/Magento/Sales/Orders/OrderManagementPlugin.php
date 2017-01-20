<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Sales\Orders;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderManagementInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkInventoryItemToMagentoStockItemCommand;

class OrderManagementPlugin
{
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function beforePlace(OrderManagementInterface $subject, OrderInterface $magentoOrder)
    {
        // We'll go through all of the items and quickly sync their stock
        array_map(function (OrderItemInterface $magentoOrderItem) {
            if (ProductType::TYPE_SIMPLE !== $magentoOrderItem->getProductType()) {
                return;
            }

            $command = new SyncSkyLinkInventoryItemToMagentoStockItemCommand();
            $command->magentoProductId = $magentoOrderItem->getProductId(); // No need to check, a product type can't be set unless there's a product ID

            $this->commandBus->handle($command);
        }, $magentoOrder->getItems());

        return $magentoOrder;
    }
}
