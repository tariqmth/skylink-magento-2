<?php

namespace spec\RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotLinkedToSkyLinkProductException;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotProductBasedException;
use RetailExpress\SkyLink\Model\Sales\Orders\SkyLinkOrderItemBuilder;

class SkyLinkOrderItemBuilderSpec extends ObjectBehavior
{
    private $magentoProductRepository;

    public function let(ProductRepositoryInterface $magentoProductRepository)
    {
        $this->magentoProductRepository = $magentoProductRepository;

        $this->beConstructedWith($this->magentoProductRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkOrderItemBuilder::class);
    }

    public function it_validates_an_order_item_is_based_off_a_magento_product(OrderItemInterface $magentoOrderItem)
    {
        $magentoOrderItem->getProductId()->willReturn(null);
        $magentoOrderItem->getItemId()->willReturn(1);

        $this
            ->shouldThrow(MagentoOrderItemNotProductBasedException::class)
            ->duringBuildFromMagentoOrderItem($magentoOrderItem);
    }

    public function it_validates_an_order_item_is_linked_to_a_skylink_product(
        OrderItemInterface $magentoOrderItem,
        ProductInterface $magentoProduct
    ) {
        $magentoOrderItem->getProductId()->willReturn($productId = 123);

        $this
            ->magentoProductRepository->getById($productId, true, null, true)
            ->willReturn($magentoProduct);

        $magentoProduct->getCustomAttribute('skylink_product_id')->willReturn(null);

        $this
            ->shouldThrow(MagentoOrderItemNotLinkedToSkyLinkProductException::class)
            ->duringBuildFromMagentoOrderItem($magentoOrderItem);
    }

    public function it_builds_a_skylink_order_item(
        OrderItemInterface $magentoOrderItem,
        ProductInterface $magentoProduct,
        AttributeInterface $skyLinkProductIdAttribute
    ) {
        $magentoOrderItem->getProductId()->willReturn($productId = 123);

        $this->magentoProductRepository
            ->getById($productId, true, null, true)
            ->willReturn($magentoProduct);

        $magentoProduct->getCustomAttribute('skylink_product_id')->willReturn($skyLinkProductIdAttribute);

        // Stubbing out for successul order item creation
        $skyLinkProductIdAttribute->getValue()->willReturn($skyLinkProductIdInteger = 124001);
        $magentoOrderItem->getQtyOrdered()->willReturn($qtyOrdered = 1.0);
        $magentoOrderItem->getQtyShipped()->willReturn($qtyShipped = 0.0);
        $magentoOrderItem->getPrice()->willReturn($price = 100.00);
        $magentoOrderItem->getTaxAmount()->willReturn($taxAmount = 10.00);

        // Build the item
        $skyLinkOrderItem = $this->buildFromMagentoOrderItem($magentoOrderItem);

        // Validate the results
        $skyLinkOrderItem->getProductId()->toNative()->shouldBe($skyLinkProductIdInteger);
        $skyLinkOrderItem->getQty()->getOrdered()->toNative()->shouldBe($qtyOrdered);
        $skyLinkOrderItem->getQty()->getFulfilled()->toNative()->shouldBe($qtyShipped);
        $skyLinkOrderItem->getPrice()->toNative()->shouldBe($price);
        $skyLinkOrderItem->getTaxRate()->toNative()->shouldBe($taxAmount / $price);
    }
}
