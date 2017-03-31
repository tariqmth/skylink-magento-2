<?php

namespace RetailExpress\SkyLink\Commands\Sales\Payments;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentBuilderInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Payments\SkyLinkPaymentAlreadyCreatedForMagentoInvoiceException;
use RetailExpress\SkyLink\Model\Sales\Invoices\InvoiceExtensionAttributes;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentRepositoryFactory;

class CreateSkyLinkPaymentFromMagentoInvoiceHandler
{
    use InvoiceExtensionAttributes;

    private $magentoInvoiceRepository;

    private $skyLinkPaymentBuilder;

    private $skyLinkPaymentRepositoryFactory;

    private $logger;

    /**
     * Event Manager instance.
     *
     * @var EventManagerInterface
     */
    private $eventManager;

    public function __construct(
        InvoiceRepositoryInterface $magentoInvoiceRepository,
        InvoiceExtensionFactory $invoiceExtensionFactory,
        SkyLinkPaymentBuilderInterface $skyLinkPaymentBuilder,
        PaymentRepositoryFactory $skyLinkPaymentRepositoryFactory,
        EventManagerInterface $eventManager,
        SkyLinkLoggerInterface $logger
    ) {
        $this->magentoInvoiceRepository = $magentoInvoiceRepository;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->skyLinkPaymentBuilder = $skyLinkPaymentBuilder;
        $this->skyLinkPaymentRepositoryFactory = $skyLinkPaymentRepositoryFactory;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }

    public function handle(CreateSkyLinkPaymentFromMagentoInvoiceCommand $command)
    {
        /* @var \Magento\Sales\Api\Data\InvoiceInterface $magentoInvoice */
        $magentoInvoice = $this->magentoInvoiceRepository->get($command->magentoInvoiceId);

        /* @var \Magento\Sales\Api\Data\InvoiceExtensionInterfac $extendedAttributes */
        $extendedAttributes = $this->getInvoiceExtensionAttributes($magentoInvoice);

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentId|null $existingSkyLinkPaymentId */
        $existingSkyLinkPaymentId = $extendedAttributes->getSkylinkPaymentId();

        if (null !== $existingSkyLinkPaymentId) {
            $e = SkyLinkPaymentAlreadyCreatedForMagentoInvoiceException::withSkyLinkPaymentIdAndMagentoInvoiceIncrementId(
                $existingSkyLinkPaymentId,
                $magentoInvoice->getIncrementId()
            );

            $this->logger->error('Attemping to create a duplicate payment in Retail Express for the same invoice in Magento.', [
                'Error' => $e->getMessage(),
                'Magento Invoice ID' => $magentoInvoice->getEntityId(),
                'Magento Invoice Increment ID' => $magentoInvoice->getIncrementId(),
                'Magento Order ID' => $magentoInvoice->getOrderId(),
            ]);

            throw $e;
        }

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\Payment $skyLinkPayment */
        $skyLinkPayment = $this->skyLinkPaymentBuilder->buildFromMagentoInvoice($magentoInvoice);

        $this->logger->info('Creating SkyLink Payment for Magento Invoice.', [
            'Magento Invoice ID' => $magentoInvoice->getEntityId(),
            'Magento Invoice Increment ID' => $magentoInvoice->getIncrementId(),
            'Magento Order ID' => $magentoInvoice->getOrderId(),
            'Total' => $skyLinkPayment->getTotal(),
            'SkyLink Payment Method ID' => $skyLinkPayment->getMethodId(),
            'Using Voucher' => $skyLinkPayment->usesVoucherCode(),
        ]);

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentRepository $skyLinkPaymentRepository */
        $skyLinkPaymentRepository = $this->skyLinkPaymentRepositoryFactory->create();

        // Add the payment in the repository
        $skyLinkPaymentRepository->add($skyLinkPayment);

        $this->eventManager->dispatch(
            'retail_express_skylink_skylink_payment_was_created_from_magento_invoice',
            [
                'command' => $command,
                'magento_invoice' => $magentoInvoice,
                'skylink_payment' => $skyLinkPayment,
            ]
        );
    }
}
