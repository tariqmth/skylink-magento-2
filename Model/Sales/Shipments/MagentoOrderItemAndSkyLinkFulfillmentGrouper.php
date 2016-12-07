<?php

namespace RetailExpress\SkyLink\Model\Sales\Shipments;

use Magento\Sales\Model\Order\Item;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoOrderItemAndSkyLinkFulfillmentGrouperInterface;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Batch as SkyLinkFulfillmentBatch;
use RetailExpress\SkyLink\Sdk\Sales\Orders\ItemId as SkyLinkOrderItemId;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Fulfillment as SkyLinkFulfillment;

class MagentoOrderItemAndSkyLinkFulfillmentGrouper implements MagentoOrderItemAndSkyLinkFulfillmentGrouperInterface
{
    private $magentoSimpleProductRepository;

    public function __construct(MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository)
    {
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function group(
        SkyLinkOrder $skyLinkOrder,
        SkyLinkFulfillmentBatch $skyLinkFulfillmentBatch,
        array $magentoOrderItems
    ) {
        $skyLinkFulfillments = $skyLinkFulfillmentBatch->getFulfillments();

        return array_map(function (SkyLinkFulfillment $skyLinkFulfillment) use ($skyLinkOrder, $magentoOrderItems) {

            /* @var SkyLinkOrderItemId $skyLinkOrderItemId */
            $skyLinkOrderItemId = $skyLinkFulfillment->getOrderItemId();

            /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\Item $skyLinkOrderItem */
            $skyLinkOrderItem = $skyLinkOrder->getItemWithId($skyLinkOrderItemId);

            /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId $skyLinkProductId */
            $skyLinkProductId = $skyLinkOrderItem->getProductId();

            // Now we have traversed our way through objects to find the SkyLink
            // Product ID, we are able to find it's matching Magento Product ID.
            // @todo check the Magento Product exists?
            /* @var \Magento\Catalog\Api\Data\ProductInterfac|null $magentoProduct */
            $magentoProduct = $this->magentoSimpleProductRepository->findBySkyLinkProductId($skyLinkProductId);

            // Find the matching order item
            $magentoOrderItem = array_first(
                $magentoOrderItems,
                function ($key, Item $magentoOrderItem) use ($magentoProduct) {
                    return $magentoOrderItem->getProductId() == $magentoProduct->getId(); // Non-strict comparison
                },
                function () use ($magentoProduct) {
                    // @todo Throw exception because product is not in order items?
                }
            );

            return [$skyLinkFulfillment, $magentoOrderItem];
        }, $skyLinkFulfillments);
    }
}
