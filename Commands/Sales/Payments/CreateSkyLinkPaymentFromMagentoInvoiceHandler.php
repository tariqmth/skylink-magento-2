<?php

namespace RetailExpress\SkyLink\Commands\Sales\Payments;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentBuilderInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Payments\SkyLinkPaymentAlreadyCreatedForMagentoInvoiceException;
use RetailExpress\SkyLink\Exceptions\Sales\Payments\SkyLinkOrderIdRequiredForMagentoOrderException;
use RetailExpress\SkyLink\Model\Sales\Invoices\InvoiceExtensionAttributes;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentRepositoryFactory;
use RuntimeException;
use RetailExpress\SkyLink\Commands\Sales\Orders\CreateSkyLinkOrderFromMagentoOrderCommand;
use RetailExpress\SkyLink\Commands\Sales\Orders\CreateSkyLinkOrderFromMagentoOrderHandler;

class CreateSkyLinkPaymentFromMagentoInvoiceHandler
{
    const MAX_ATTEMPTS = 6;
    const ATTEMPTS_DELAY = 10;

    use InvoiceExtensionAttributes;

    private $magentoInvoiceRepository;

    private $skyLinkPaymentBuilder;

    private $skyLinkPaymentRepositoryFactory;

    private $logger;

    private $orderHandler;

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
        SkyLinkLoggerInterface $logger,
        CreateSkyLinkOrderFromMagentoOrderHandler $orderHandler
    ) {
        $this->magentoInvoiceRepository = $magentoInvoiceRepository;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->skyLinkPaymentBuilder = $skyLinkPaymentBuilder;
        $this->skyLinkPaymentRepositoryFactory = $skyLinkPaymentRepositoryFactory;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->orderHandler = $orderHandler;
    }

    public function handle(CreateSkyLinkPaymentFromMagentoInvoiceCommand $command)
    {
        $attempts = 0;
        do {
            try {
                return $this->doHandle($command);
            } catch (SkyLinkOrderIdRequiredForMagentoOrderException $e) {
                sleep(self::ATTEMPTS_DELAY);
                // We probably tried the command too early, let's fail out
            }
        } while ($attempts++ < self::MAX_ATTEMPTS);

        // Try forcing an order sync if it hasn't happened already
        $magentoOrderId = $this->magentoInvoiceRepository->get($command->magentoInvoiceId)->getOrderId();
        $this->logger->info('Order did not have Skylink ID, syncing order and trying again.', [
            'Magento Order ID' => $magentoOrderId
        ]);
        $orderSyncCommand = new CreateSkyLinkOrderFromMagentoOrderCommand();
        $orderSyncCommand->magentoOrderId = $magentoOrderId;
        $this->orderHandler->handle($orderSyncCommand);
        return $this->doHandle($command);
    }

    private function doHandle(CreateSkyLinkPaymentFromMagentoInvoiceCommand $command)
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
