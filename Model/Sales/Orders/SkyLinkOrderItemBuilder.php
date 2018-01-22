<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use InvalidArgumentException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as MagentoOrderItem;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderItemBuilderInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemContainsMoreThanOneChildItemException;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotLinkedToSkyLinkProductException;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotProductBasedException;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Item as SkyLinkOrderItem;

class SkyLinkOrderItemBuilder implements SkyLinkOrderItemBuilderInterface
{
    private $magentoProductRepository;

    public function __construct(ProductRepositoryInterface $magentoProductRepository)
    {
        $this->magentoProductRepository = $magentoProductRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Use logic from the Magento 1 Plugin to support bundled products.
     */
    public function buildFromMagentoOrderItem(OrderItemInterface $magentoOrderItem)
    {
        $this->assertImplementationOfOrderItem($magentoOrderItem);

        // If the item has children, we'll make sure there's only one child and that way we can take
        // pricing from the main order item but take the SkyLink Product ID information from the
        // first child item
        if (!$magentoOrderItem->getHasChildren()) {
            $skyLinkProductIdAttribute = $this->getSkyLinkProductIdAttribute($magentoOrderItem);
        } else {
            $magentoChildrenOrderItems = $magentoOrderItem->getChildrenItems();

            if (count($magentoChildrenOrderItems) > 1) {
                throw MagentoOrderItemContainsMoreThanOneChildItemException::withMagentoOrderItem($magentoOrderItem);
            }

            $skyLinkProductIdAttribute = $this->getSkyLinkProductIdAttribute($magentoChildrenOrderItems[0]);
        }

        return SkyLinkOrderItem::fromNative(
            $skyLinkProductIdAttribute->getValue(),
            $magentoOrderItem->getQtyOrdered(),
            $magentoOrderItem->getQtyShipped(), // @todo Should this always be 0?
            $magentoOrderItem->getPriceInclTax() - $magentoOrderItem->getDiscountAmount(),
            $magentoOrderItem->getTaxAmount() / ($magentoOrderItem->getPrice() * $magentoOrderItem->getQtyOrdered())
        );
    }

    /**
     * @return \Magento\Framework\Api\AttributeInterface
     */
    private function getSkyLinkProductIdAttribute(OrderItemInterface $magentoOrderItem)
    {
        $magentoProductId = $magentoOrderItem->getProductId();

        if (null === $magentoProductId) {
            throw MagentoOrderItemNotProductBasedException::withMagentoOrderItem($magentoOrderItem);
        }

        /* @var \Magento\Catalog\Api\Data\ProductInterface $magentoProduct */
        $magentoProduct = $this->magentoProductRepository->getById(
            $magentoProductId,
            true,
            null,
            true
        );

        /* @var \Magento\Framework\Api\AttributeInterface|null $skylinkProductIdAttribute */
        $skyLinkProductIdAttribute = $magentoProduct->getCustomAttribute('skylink_product_id');

        if (null === $skyLinkProductIdAttribute) {
            throw MagentoOrderItemNotLinkedToSkyLinkProductException::withMagentoProductId($magentoProductId);
        }

        return $skyLinkProductIdAttribute;
    }

    private function assertImplementationOfOrderItem(OrderItemInterface $magentoOrderItem)
    {
        if (!$magentoOrderItem instanceof MagentoOrderItem) {
            throw new InvalidArgumentException(sprintf(
                'Determining a Pickup Outlet requires a Magento Order be an instance of %s, %s given.',
                magentoOrder::class,
                get_class($magentoOrderItem)
            ));
        }
    }
}
