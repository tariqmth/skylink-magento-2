<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderItemBuilderInterface;
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
     */
    public function buildFromMagentoOrderItem(OrderItemInterface $magentoOrderItem)
    {
        $magentoProductId = $magentoOrderItem->getProductId();

        if (null === $magentoProductId) {
            throw MagentoOrderItemNotProductBasedException::withMagentoOrderItem($magentoOrderItem);
        }

        /* @var \Magento\Catalog\Api\Data\ProductInterface $magentoProduct */
        $magentoProduct = $this->magentoProductRepository->getById($magentoProductId);

        /* @var Magento\Framework\Api\AttributeInterface|null $skylinkProductIdAttribute */
        $skyLinkProductIdAttribute = $magentoProduct->getCustomAttribute('skylink_product_id');

        if (null === $skyLinkProductIdAttribute) {
            throw MagentoOrderItemNotLinkedToSkyLinkProductException::withMagentoProductId($magentoProductId);
        }

        return SkyLinkOrderItem::fromNative(
            $skyLinkProductIdAttribute->getValue(),
            $magentoOrderItem->getQtyOrdered(),
            $magentoOrderItem->getQtyShipped(), // @todo Should this always be 0?
            $magentoOrderItem->getPrice(),
            $magentoOrderItem->getTaxAmount() / $magentoOrderItem->getPrice()
        );
    }
}
