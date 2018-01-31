<?php

namespace RetailExpress\SkyLink\Commands\Sales\Payments;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\SkyLinkOrderDoesNotExistException;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepositoryFactory as SkyLinkOrderRepositoryFactory;
use Magento\Sales\Api\InvoiceOrderInterface;

class CreateMagentoInvoiceFromSkyLinkPaymentHandler
{
    private $magentoOrderRepository;

    private $skyLinkOrderRepositoryFactory;

    private $invoiceOrder;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    /**
     * Event Manager instance.
     *
     * @var EventManagerInterface
     */
    private $eventManager;

    public function __construct(
        MagentoOrderRepositoryInterface $magentoOrderRepository,
        SkyLinkOrderRepositoryFactory $skyLinkOrderRepositoryFactory,
        SkyLinkLoggerInterface $logger,
        EventManagerInterface $eventManager,
        InvoiceOrderInterface $invoiceOrder
    ) {
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->skyLinkOrderRepositoryFactory = $skyLinkOrderRepositoryFactory;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->invoiceOrder = $invoiceOrder;
    }

    public function handle(CreateMagentoInvoiceFromSkyLinkPaymentCommand $command)
    {
        /* @var \SkyLinkOrderId $skyLinkOrderId */
        $skyLinkOrderId = new SkyLinkOrderId($command->skyLinkOrderId);

        /* @var \Magento\Sales\Api\Data\OrderInterface $magentoOrder */
        $magentoOrder = $this->magentoOrderRepository->findBySkyLinkOrderId($skyLinkOrderId);

        $this->logger->info('Syncing all SkyLink Payments to Magento Invoice for SkyLink Order.', [
            'Magento Order ID' => $magentoOrder->getIncrementId(),
            'SkyLink Order ID' => $skyLinkOrderId,
        ]);

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepository $skyLinkOrderRepository */
        $skyLinkOrderRepository = $this->skyLinkOrderRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\Order $skyLinkOrder */
        $skyLinkOrder = $skyLinkOrderRepository->get($skyLinkOrderId);

        if (null === $skyLinkOrder) {
            $e = SkyLinkOrderDoesNotExistException::withSkyLinkOrderId($skyLinkOrderId);

            $this->logger->error('Cannot find SkyLink Order to sync Payments for. Has it been cancelled in Retail Express and hidden from the API?', [
                'Error' => $e->getMessage(),
                'Magento Order ID' => $magentoOrder->getIncrementId(),
                'SkyLink Order ID' => $skyLinkOrderId,
            ]);

            throw $e;
        }

        if (!$magentoOrder->getBaseTotalDue() || !$magentoOrder->canInvoice()) {
            $this->logger->debug('Order has already been paid in Magento or cannot be further invoiced. ' .
                'Skipping payments sync.', [
                'Magento Order ID' => $magentoOrder->getIncrementId(),
                'SkyLink Order ID' => $skyLinkOrderId,
            ]);
            return;
        }

        $skyLinkPaymentTotal = 0;
        foreach ($skyLinkOrder->getPayments() as $skyLinkPayment) {
            $skyLinkPaymentTotal += $skyLinkPayment->getTotal()->toNative();
        }

        if ($skyLinkPaymentTotal < $magentoOrder->getBaseTotalDue()) {
            $this->logger->debug('There are no Payments for SkyLink Order, or the total paid was less ' .
                'than the order balance in Magento. Skipping payments sync.', [
                'Magento Order ID' => $magentoOrder->getIncrementId(),
                'SkyLink Order ID' => $skyLinkOrderId
            ]);
            return;
        }

        $invoiceId = $this->invoiceOrder->execute($magentoOrder->getId());

        $this->eventManager->dispatch(
            'retail_express_skylink_skylink_payments_were_synced_to_magento_invoice',
            [
                'command' => $command,
                'skylink_order' => $skyLinkOrder,
                'magento_order' => $magentoOrder,
                'magento_invoice' => $invoiceId
            ]
        );
    }
}
