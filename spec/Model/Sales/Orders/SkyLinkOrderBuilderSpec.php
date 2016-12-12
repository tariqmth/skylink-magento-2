<?php

namespace spec\RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderAddressExtractorInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkContactBuilderInterface as SkyLinkOrderContactBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkCustomerIdServiceInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderItemBuilderInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderStateNotMappedException;
use RetailExpress\SkyLink\Model\Sales\Orders\SkyLinkOrderBuilder;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Item as SkyLinkOrderItem;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Status as SkyLinkOrderStatus;

class SkyLinkOrderBuilderSpec extends ObjectBehavior
{
    private $skyLinkCustomerIdService;

    private $magentoOrderAddressExtractor;

    private $skyLinkOrderContactBuilder;

    private $skyLinkOrderItemBuilder;

    public function let(
        SkyLinkCustomerIdServiceInterface $skyLinkCustomerIdService,
        MagentoOrderAddressExtractorInterface $magentoOrderAddressExtractor,
        SkyLinkOrderContactBuilderInterface $skyLinkOrderContactBuilder,
        SkyLinkOrderItemBuilderInterface $skyLinkOrderItemBuilder
    ) {
        $this->skyLinkCustomerIdService = $skyLinkCustomerIdService;
        $this->magentoOrderAddressExtractor = $magentoOrderAddressExtractor;
        $this->skyLinkOrderContactBuilder = $skyLinkOrderContactBuilder;
        $this->skyLinkOrderItemBuilder = $skyLinkOrderItemBuilder;

        $this->beConstructedWith(
            $this->skyLinkCustomerIdService,
            $this->magentoOrderAddressExtractor,
            $this->skyLinkOrderContactBuilder,
            $this->skyLinkOrderItemBuilder
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkOrderBuilder::class);
    }

    public function it_builds_an_order(
        OrderInterface $magentoOrder,
        OrderAddressInterface $magentoBillingAddress,
        OrderAddressInterface $magentoShippingAddress,
        SkyLinkBillingContact $skyLinkBillingContact,
        SkyLinkShippingContact $skyLinkShippingContact,
        OrderItemInterface $magentoOrderItem,
        SkyLinkOrderItem $skyLinkOrderItem
    ) {
        $valuesToAssert = $this->setupMagentoOrderStubs(
            $magentoOrder,
            $magentoBillingAddress,
            $magentoShippingAddress,
            $skyLinkBillingContact,
            $skyLinkShippingContact,
            $magentoOrderItem,
            $skyLinkOrderItem
        );

        $skyLinkOrder = $this->buildFromMagentoOrder($magentoOrder);

        $skyLinkOrder->getId()->shouldBe(null);
        $skyLinkOrder->getCustomerId()->sameValueAs($valuesToAssert['skyLinkCustomerId'])->shouldBe(true);
        $skyLinkOrder->getPlacedAt()->getTimestamp()->shouldBe(strtotime($valuesToAssert['createdAt']));
        $skyLinkOrder->getStatus()->shouldBe(SkyLinkOrderStatus::get('pending')); // @todo should we use $valuesToAssert?
        $skyLinkOrder->getShippingCharge()->getPrice()->toNative()->shouldBe($valuesToAssert['shippingAmount']);
        $skyLinkOrder->getShippingCharge()->getTaxRate()->toNative()->shouldBe(
            $valuesToAssert['shippingTaxAmount'] / $valuesToAssert['shippingAmount']
        );

        $skyLinkOrder->getItems()->shouldHaveCount(1);

        // @todo validate billing/shipping contact and items better
    }

    public function it_throws_an_exception_when_a_magento_order_with_an_unmapped_state_appears(
        OrderInterface $magentoOrder,
        OrderAddressInterface $magentoBillingAddress,
        OrderAddressInterface $magentoShippingAddress,
        SkyLinkBillingContact $skyLinkBillingContact,
        SkyLinkShippingContact $skyLinkShippingContact,
        OrderItemInterface $magentoOrderItem,
        SkyLinkOrderItem $skyLinkOrderItem
    ) {
        $valuesToAssert = $this->setupMagentoOrderStubs(
            $magentoOrder,
            $magentoBillingAddress,
            $magentoShippingAddress,
            $skyLinkBillingContact,
            $skyLinkShippingContact,
            $magentoOrderItem,
            $skyLinkOrderItem
        );

        $magentoOrder->getIncrementId()->willReturn('1-00000001');
        $magentoOrder->getState()->willReturn('an unmapped state');

        $this
            ->shouldThrow(MagentoOrderStateNotMappedException::class)
            ->duringBuildFromMagentoOrder($magentoOrder);
    }

    private function setupMagentoOrderStubs(
        OrderInterface $magentoOrder,
        OrderAddressInterface $magentoBillingAddress,
        OrderAddressInterface $magentoShippingAddress,
        SkyLinkBillingContact $skyLinkBillingContact,
        SkyLinkShippingContact $skyLinkShippingContact,
        OrderItemInterface $magentoOrderItem,
        SkyLinkOrderItem $skyLinkOrderItem
    ) {
        $skyLinkCustomerId = new SkyLinkCustomerId(300000);
        $this->skyLinkCustomerIdService->determineSkyLinkCustomerId($magentoOrder)->willReturn($skyLinkCustomerId);

        // Getting the state from the order
        $magentoOrder->getState()->willReturn(MagentoOrder::STATE_NEW);

        // Created at is used for the "placed at" field in SkyLink
        $magentoOrder->getCreatedAt()->willReturn($createdAt = '2016-01-01 00:00:00');

        // Extracting Billing Addres and building a Billing Contact
        $this->magentoOrderAddressExtractor->extractBillingAddress($magentoOrder)->willReturn($magentoBillingAddress);
        $this
            ->skyLinkOrderContactBuilder
            ->buildSkyLinkBillingContactFromMagentoOrderAddress($magentoBillingAddress)
            ->willReturn($skyLinkBillingContact);

        $this->magentoOrderAddressExtractor->extractShippingAddress($magentoOrder)->willReturn($magentoShippingAddress);
        $this
            ->skyLinkOrderContactBuilder
            ->buildSkyLinkShippingContactFromMagentoOrderAddress($magentoShippingAddress)
            ->willReturn($skyLinkShippingContact);

        // Shipping amounts
        $magentoOrder->getShippingAmount()->willReturn($shippingAmount = 10.0);
        $magentoOrder->getShippingTaxAmount()->willReturn($shippingTaxAmount = 1.0);

        // Public comments
        $magentoOrder->getCustomerNote()->willReturn($customerNote = 'Some public comment here.');

        // Order item
        $magentoOrder->getItems()->willReturn([$magentoOrderItem]);
        $this->skyLinkOrderItemBuilder->buildFromMagentoOrderItem($magentoOrderItem)->willReturn($skyLinkOrderItem);

        return compact(
            'skyLinkCustomerId',
            'createdAt',
            'shippingAmount',
            'shippingTaxAmount',
            'customerNote'
        );
    }
}
