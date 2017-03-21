<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use DateTimeImmutable;
use InvalidArgumentException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Shipping\Model\Config as ShippingConfig;
use RetailExpress\SkyLink\Api\Pickup\PickupManagementInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderAddressExtractorInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkContactBuilderInterface as SkyLinkOrderContactBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkCustomerIdServiceInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderItemBuilderInterface;
use RetailExpress\SkyLink\Api\Data\Sales\Shipments\ItemFulfillmentMethodInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderStateNotMappedException;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Status as SkyLinkStatus;
use RetailExpress\SkyLink\Sdk\Sales\Orders\ShippingCharge;
use ValueObjects\StringLiteral\StringLiteral;

class SkyLinkOrderBuilder implements SkyLinkOrderBuilderInterface
{
    private $orderConfig;

    private $skyLinkCustomerIdService;

    private $magentoOrderAddressExtractor;

    private $skyLinkOrderContactBuilder;

    private $skyLinkOrderItemBuilder;

    private $pickupManagement;

    private $magentoShippingConfig;

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
        ConfigInterface $orderConfig,
        SkyLinkCustomerIdServiceInterface $skyLinkCustomerIdService,
        MagentoOrderAddressExtractorInterface $magentoOrderAddressExtractor,
        SkyLinkOrderContactBuilderInterface $skyLinkOrderContactBuilder,
        SkyLinkOrderItemBuilderInterface $skyLinkOrderItemBuilder,
        PickupManagementInterface $pickupManagement,
        ShippingConfig $magentoShippingConfig
    ) {
        $this->orderConfig = $orderConfig;
        $this->skyLinkCustomerIdService = $skyLinkCustomerIdService;
        $this->magentoOrderAddressExtractor = $magentoOrderAddressExtractor;
        $this->skyLinkOrderContactBuilder = $skyLinkOrderContactBuilder;
        $this->skyLinkOrderItemBuilder = $skyLinkOrderItemBuilder;
        $this->pickupManagement = $pickupManagement;
        $this->magentoShippingConfig = $magentoShippingConfig;
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

        $skyLinkOrder = $skyLinkOrder->withPublicComments($this->determinePublicComments($magentoOrder));

        $pickupOutlet = $this->determinePickupOutlet($magentoOrder);
        if (null !== $pickupOutlet) {
            $skyLinkOrder = $skyLinkOrder->fulfillFromOutletId($pickupOutlet->getId());
        }

        // Add order items
        array_map(function (OrderItemInterface $magentoOrderItem) use (&$skyLinkOrder) {

            // We get an array of all order items, parents and children. The Order Item Builder is responsible
            // for translating any type of order item (parent or child) into a SkyLink Order Item. Because
            // of this, we'll only send order items that don't have a parent (so as to let it handle
            // all order item types). This is how we handle (for example) both
            // simple and configurable products. Cool, eh?
            if ($magentoOrderItem->getParentItemId()) {
                return;
            }

            $skyLinkOrderItem = $this->skyLinkOrderItemBuilder->buildFromMagentoOrderItem($magentoOrderItem);
            $skyLinkOrder = $skyLinkOrder->withItem($skyLinkOrderItem);
        }, $magentoOrder->getItems());

        $skyLinkOrder = $skyLinkOrder->withFulfillmentMethodForAllItems(
            $this->determineItemFulfillmentMethod($magentoOrder)
        );

        $skyLinkOrder = $skyLinkOrder->withDeliveryDriverNameForAllItems(
            new StringLiteral($this->getShippingCarrierTitle($magentoOrder))
        );

        return $skyLinkOrder;
    }

    private function determinePublicComments(OrderInterface $magentoOrder)
    {
        $publicComments = [];

        if ($shippingDescription = $magentoOrder->getShippingDescription()) {
            $publicComments[] = __('Shipping: %1', $shippingDescription);
        }

        if ($customerNote = $magentoOrder->getCustomerNote()) {
            $publicComments[] = __('Customer Note: %1', $customerNote);
        }

        return new StringLiteral(implode("\n", $publicComments));
    }

    /**
     * @see \Magento\Checkout\Model\Type\Onepage::saveShippingMethod()
     */
    private function getShippingCarrierTitle(OrderInterface $magentoOrder)
    {
        $shippingDescription = $magentoOrder->getShippingDescription();

        // This is designed to take the carrier title from a shipping description in the following forms
        // (as per Onepage::saveShippingMethod):
        // 1. "My Carrier - My Method"
        // 2. "My carrier"
        list($carrierTitle) = explode(' - ', $shippingDescription, 2);

        return $carrierTitle;
    }

    private function determineItemFulfillmentMethod(OrderInterface $magentoOrder)
    {
        $this->assertImplementationOfOrder($magentoOrder);

        $carrierCode = $magentoOrder->getShippingMethod(true)->getData('carrier_code');
        $matchingCarrier = $this->magentoShippingConfig->getActiveCarriers()[$carrierCode]; // @todo somehow validate the carrier??

        // If our class adheres to the item fulfillment method contract, we'll use it to determine the method
        if ($matchingCarrier instanceof ItemFulfillmentMethodInterface) {
            return $matchingCarrier->getItemFulfillmentMethod();
        }

        // Failing that, we'll just use the default configured one
        return $this->orderConfig->getItemFulfillmentMethod();
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
        $price = $magentoOrder->getShippingAmount();

        if ($price > 0) {
            $taxRate = $magentoOrder->getShippingTaxAmount() / $price;
        } else {
            $taxRate = 0;
        }

        return ShippingCharge::fromNative($price, $taxRate);
    }

    private function determinePickupOutlet(OrderInterface $magentoOrder)
    {
        $this->assertImplementationOfOrder($magentoOrder);

        return $this->pickupManagement->determineSkyLinkOutletToPickupFrom($magentoOrder);
    }

    private function assertImplementationOfOrder(OrderInterface $magentoOrder)
    {
        if (!$magentoOrder instanceof MagentoOrder) {
            throw new InvalidArgumentException(sprintf(
                'Determining a Pickup Outlet requires a Magento Order be an instance of %s.',
                MagentoOrder::class
            ));
        }
    }
}
