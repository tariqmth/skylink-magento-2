<?php

namespace RetailExpress\SkyLink\Commands\Sales\Payments;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentBuilderInterface;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentRepositoryFactory;

class CreateSkyLinkPaymentFromMagentoInvoiceHandler
{
    private $magentoInvoiceRepository;

    private $skyLinkPaymentBuilder;

    private $skyLinkPaymentRepositoryFactory;

    /**
     * Event Manager instance.
     *
     * @var EventManagerInterface
     */
    private $eventManager;

    public function __construct(
        InvoiceRepositoryInterface $magentoInvoiceRepository,
        SkyLinkPaymentBuilderInterface $skyLinkPaymentBuilder,
        PaymentRepositoryFactory $skyLinkPaymentRepositoryFactory,
        EventManagerInterface $eventManager
    ) {
        $this->magentoInvoiceRepository = $magentoInvoiceRepository;
        $this->skyLinkPaymentBuilder = $skyLinkPaymentBuilder;
        $this->skyLinkPaymentRepositoryFactory = $skyLinkPaymentRepositoryFactory;
        $this->eventManager = $eventManager;
    }

    public function handle(CreateSkyLinkPaymentFromMagentoInvoiceCommand $command)
    {
        /* @var \Magento\Sales\Api\Data\InvoiceInterface $magentoInvoice */
        $magentoInvoice = $this->magentoInvoiceRepository->get($command->magentoInvoiceId);

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\Payment $skyLinkPayment */
        $skyLinkPayment = $this->skyLinkPaymentBuilder->buildFromMagentoInvoice($magentoInvoice);

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
