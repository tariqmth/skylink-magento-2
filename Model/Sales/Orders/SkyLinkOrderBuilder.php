<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use DateTimeImmutable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderAddressExtractorInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkCustomerIdServiceInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderBuilderInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotLinkedToSkyLinkProductException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Item as SkyLinkOrderItem;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Status as SkyLinkStatus;
use RetailExpress\SkyLink\Sdk\Sales\Orders\ShippingCharge;
use ValueObjects\StringLiteral\StringLiteral;

/**
 * @todo refactor this class so it's testable
 */
class SkyLinkOrderBuilder implements SkyLinkOrderBuilderInterface
{
    private $skyLinkCustomerIdService;

    private $magentoOrderAddressExtractor;

    private $magentoProductRepository;

    private static function getMagentoStateToSkyLinkStatusMappings()
    {
        $defaultMappings = [
            MagentoOrder::STATE_NEW => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_PENDING_PAYMENT => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_PROCESSING => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_COMPLETE => SkyLinkStatus::COMPLETE,
            MagentoOrder::STATE_CLOSED => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_CANCELED => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_HOLDED => SkyLinkStatus::PROCESSING,
            MagentoOrder::STATE_PAYMENT_REVIEW => SkyLinkStatus::PROCESSING,
        ];

        // @todo See __construct()
        $mappingOverrides = [];

        return array_merge($defaultMappings, $mappingOverrides);
    }

    /**
     * @todo allow custom Magento State to SkyLink Status mappings to be injected.
     */
    public function __construct(
        SkyLinkCustomerIdServiceInterface $skyLinkCustomerIdService,
        MagentoOrderAddressExtractorInterface $magentoOrderAddressExtractor,
        ProductRepositoryInterface $magentoProductRepository
    ) {
        $this->skyLinkCustomerIdService = $skyLinkCustomerIdService;
        $this->magentoOrderAddressExtractor = $magentoOrderAddressExtractor;
        $this->magentoProductRepository = $magentoProductRepository;
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

        $publicComments = $this->getPublicComments($magentoOrder);

        if (null !== $publicComments) {
            $skyLinkOrder = $skyLinkOrder->withPublicComments(new StringLiteral($publicComments));
        }

        // Add order items
        array_map(function (OrderItemInterface $magentoOrderItem) use (&$skyLinkOrder) {
            $skyLinkOrderItem = $this->getSkyLinkOrderItem($magentoOrderItem);
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

        return SkyLinkStatus::get($mappings[$magentoOrder->getState()]);
    }

    /**
     * @todo See RetailExpress\SkyLink\Model\Customers::createBillingContact() to reduce code duplication.
     */
    private function getSkyLinkBillingContact(OrderInterface $magentoOrder)
    {
        $magentoBillingAddress = $this->magentoOrderAddressExtractor->extractBillingAddress($magentoOrder);

        return forward_static_call_array(
            [SkyLinkBillingContact::class, 'fromNative'],
            $this->getBillingContactArguments($magentoBillingAddress)
        );
    }

    /**
     * @todo See RetailExpress\SkyLink\Model\Customers::createShippingContact() to reduce code duplication.
     */
    private function getSkyLinkShippingContact(OrderInterface $magentoOrder)
    {
        $magentoShippingAddress = $this->magentoOrderAddressExtractor->extractShippingAddress($magentoOrder);

        $arguments = $this->getBillingContactArguments($magentoShippingAddress);
        unset($arguments[2]); // Email
        unset($arguments[11]); // Fax

        return forward_static_call_array([SkyLinkShippingContact::class, 'fromNative'], $arguments);
    }

    /**
     * @todo See RetailExpress\SkyLink\Model\Customers::getBillingContactArguments() to reduce code duplication.
     */
    private function getBillingContactArguments(OrderAddressInterface $magentoOrderAddress)
    {
        $addressLines = $magentoOrderAddress->getStreet() ?: [];

        return [
            (string) $magentoOrderAddress->getFirstname(),
            (string) $magentoOrderAddress->getLastname(),
            (string) $magentoOrderAddress->getEmail(),
            (string) $magentoOrderAddress->getCompany(),
            array_get($addressLines, 0, ''),
            array_get($addressLines, 1, ''),
            (string) $magentoOrderAddress->getCity(),
            (string) $magentoOrderAddress->getCity(),
            (string) $magentoOrderAddress->getRegionCode(),
            (string) $magentoOrderAddress->getPostcode(),
            (string) $magentoOrderAddress->getCountryId(),
            (string) $magentoOrderAddress->getTelephone(),
            (string) $magentoOrderAddress->getFax(),
        ];
    }

    private function getShippingCharge(OrderInterface $magentoOrder)
    {
        return ShippingCharge::fromNative(
            $magentoOrder->getShippingAmount(),
            $magentoOrder->getShippingTaxAmount() / $magentoOrder->getShippingAmount() // @todo is this right??
        );
    }

    private function getPublicComments(OrderInterface $magentoOrder)
    {
        return $magentoOrder->getCustomerNote();
    }

    private function getSkyLinkOrderItem(OrderItemInterface $magentoOrderItem)
    {
        $magentoProductId = $magentoOrderItem->getProductId(); // @todo null check
        $magentoProduct = $this->magentoProductRepository->getById($magentoProductId);

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
