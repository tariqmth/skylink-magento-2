<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use DateTimeImmutable;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderAddressExtractorInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkContactBuilderInterface as SkyLinkOrderContactBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkCustomerIdServiceInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderItemBuilderInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderStateNotMappedException;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Status as SkyLinkStatus;
use RetailExpress\SkyLink\Sdk\Sales\Orders\ShippingCharge;
use ValueObjects\StringLiteral\StringLiteral;

class SkyLinkOrderBuilder implements SkyLinkOrderBuilderInterface
{
    private $skyLinkCustomerIdService;

    private $magentoOrderAddressExtractor;

    private $skyLinkOrderContactBuilder;

    private $skyLinkOrderItemBuilder;

    /**
     * Returns the Magento State to SkyLink Status mappings.
     *
     * @return array
     */
    private static function getMagentoStateToSkyLinkStatusMappings()
    {
        $defaultMappings = [
            MagentoOrder::STATE_NEW => SkyLinkStatus::PENDING,
            MagentoOrder::STATE_PENDING_PAYMENT => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_PROCESSING => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_COMPLETE => SkyLinkStatus::COMPLETE,
            MagentoOrder::STATE_CLOSED => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_CANCELED => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_HOLDED => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_PAYMENT_REVIEW => SkyLinkStatus::PROCESSING,
        ];

        // @todo allow custom Magento State to SkyLink Status mappings to be injected through the constructor
        $mappingOverrides = [];

        return array_merge($defaultMappings, $mappingOverrides);
    }

    public function __construct(
        SkyLinkCustomerIdServiceInterface $skyLinkCustomerIdService,
        MagentoOrderAddressExtractorInterface $magentoOrderAddressExtractor,
        SkyLinkOrderContactBuilderInterface $skyLinkOrderContactBuilder,
        SkyLinkOrderItemBuilderInterface $skyLinkOrderItemBuilder
    ) {
        $this->skyLinkCustomerIdService = $skyLinkCustomerIdService;
        $this->magentoOrderAddressExtractor = $magentoOrderAddressExtractor;
        $this->skyLinkOrderContactBuilder = $skyLinkOrderContactBuilder;
        $this->skyLinkOrderItemBuilder = $skyLinkOrderItemBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFromMagentoOrder(OrderInterface $magentoOrder)
    {
        $skyLinkOrder = SkyLinkOrder::forCustomerWithId(
            $this->skyLinkCustomerIdService->determineSkyLinkCustomerId($magentoOrder),
            $this->getPlacedAt($magentoOrder),
            $this->getSkyLinkStatus($magentoOrder),
            $this->getSkyLinkBillingContact($magentoOrder),
            $this->getSkyLinkShippingContact($magentoOrder),
            $this->getShippingCharge($magentoOrder)
        );

        if (null !== $magentoOrder->getCustomerNote()) {
            $skyLinkOrder = $skyLinkOrder->withPublicComments(new StringLiteral($magentoOrder->getCustomerNote()));
        }

        // Add order items
        array_map(function (OrderItemInterface $magentoOrderItem) use (&$skyLinkOrder) {
            $skyLinkOrderItem = $this->skyLinkOrderItemBuilder->buildFromMagentoOrderItem($magentoOrderItem);
            $skyLinkOrder = $skyLinkOrder->withItem($skyLinkOrderItem);
        }, $magentoOrder->getItems());

        return $skyLinkOrder;
    }

    private function getPlacedAt(OrderInterface $magentoOrder)
    {
        return new DateTimeImmutable($magentoOrder->getCreatedAt()); // @todo Timezones?
    }

    private function getSkyLinkStatus(OrderInterface $magentoOrder)
    {
        $mappings = self::getMagentoStateToSkyLinkStatusMappings();

        $magentoOrderState = $magentoOrder->getState();

        if (false === array_key_exists($magentoOrderState, $mappings)) {
            throw MagentoOrderStateNotMappedException::withOrder($magentoOrder);
        }

        return SkyLinkStatus::get($mappings[$magentoOrderState]);
    }

    private function getSkyLinkBillingContact(OrderInterface $magentoOrder)
    {
        $magentoBillingAddress = $this->magentoOrderAddressExtractor->extractBillingAddress($magentoOrder);

        return $this
            ->skyLinkOrderContactBuilder
            ->buildSkyLinkBillingContactFromMagentoOrderAddress($magentoBillingAddress);
    }

    private function getSkyLinkShippingContact(OrderInterface $magentoOrder)
    {
        $magentoShippingAddress = $this->magentoOrderAddressExtractor->extractShippingAddress($magentoOrder);

        return $this
            ->skyLinkOrderContactBuilder
            ->buildSkyLinkShippingContactFromMagentoOrderAddress($magentoShippingAddress);
    }

    private function getShippingCharge(OrderInterface $magentoOrder)
    {
        return ShippingCharge::fromNative(
            $magentoOrder->getShippingAmount(),
            $magentoOrder->getShippingTaxAmount() / $magentoOrder->getShippingAmount() // @todo is this right??
        );
    }
}
