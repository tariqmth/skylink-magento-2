<?php

namespace RetailExpress\SkyLink\Model\Sales\Payments;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\MagentoPaymentMethodRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentMethodRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Payments\PaymentMethodNotMappedException;
use RetailExpress\SkyLink\Exceptions\Sales\Payments\SkyLinkOrderIdRequiredForMagentoOrderException;
use RetailExpress\SkyLink\Model\Sales\Orders\OrderExtensionAttributes;
use RetailExpress\SkyLink\Sdk\Sales\Payments\Payment as SkyLinkPayment;

class SkyLinkPaymentBuilder implements SkyLinkPaymentBuilderInterface
{
    use OrderExtensionAttributes;

    private $baseMagentoOrderRepository;

    private $magentoPaymentMethodRepository;

    private $skyLinkPaymentMethodRepository;

    public function __construct(
        OrderRepositoryInterface $baseMagentoOrderRepository,
        OrderExtensionFactory $orderExtensionFactory,
        MagentoPaymentMethodRepositoryInterface $magentoPaymentMethodRepository,
        SkyLinkPaymentMethodRepositoryInterface $skyLinkPaymentMethodRepository
    ) {
        $this->baseMagentoOrderRepository = $baseMagentoOrderRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->magentoPaymentMethodRepository = $magentoPaymentMethodRepository;
        $this->skyLinkPaymentMethodRepository = $skyLinkPaymentMethodRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFromMagentoInvoice(InvoiceInterface $magentoInvoice)
    {
        /* @var OrderInterface $magentoOrder */
        $magentoOrder = $this->baseMagentoOrderRepository->get($magentoInvoice->getOrderId());
        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId $skyLinkOrderId */
        $skyLinkOrderId = $this->getSkyLinkOrderId($magentoOrder);

        /* @var string $magentoPaymentMethodCode */
        $magentoPaymentMethodCode = $magentoOrder->getPayment()->getMethod();
        /* @var \Magento\Payment\Model\MethodInterface $magentoPaymentMethod */
        $magentoPaymentMethod = $this->magentoPaymentMethodRepository->get($magentoPaymentMethodCode);

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod|null $skyLinkPaymentMethod */
        $skyLinkPaymentMethod = $this
            ->skyLinkPaymentMethodRepository
            ->getSkyLinkPaymentMethodForMagentoPaymentMethod($magentoPaymentMethod);

        if (null === $skyLinkPaymentMethod) {
            throw PaymentMethodNotMappedException::withMagentoPaymentMethod($magentoPaymentMethod);
        }

        return SkyLinkPayment::normalFromNative(
            (string) $skyLinkOrderId,
            strtotime($magentoInvoice->getCreatedAt()),
            (string) $skyLinkPaymentMethod->getId(),
            $magentoInvoice->getGrandTotal()
        );
    }

    private function getSkyLinkOrderId(OrderInterface $magentoOrder)
    {
        /* @var \Magento\Sales\Api\Data\OrderExtensionInterface $extendedAttributes */
        $extendedAttributes = $this->getOrderExtensionAttributes($magentoOrder);
        $skyLinkOrderId = $extendedAttributes->getSkyLinkOrderId();

        if (null === $skyLinkOrderId) {
            throw SkyLinkOrderIdRequiredForMagentoOrderException::withMagentoOrder($magentoOrder);
        }

        return $skyLinkOrderId;
    }
}
