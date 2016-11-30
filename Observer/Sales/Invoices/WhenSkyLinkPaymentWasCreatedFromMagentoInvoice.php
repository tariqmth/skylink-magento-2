<?php

namespace RetailExpress\SkyLink\Observer\Sales\Invoices;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use RetailExpress\SkyLink\Model\Sales\Invoices\InvoiceExtensionAttributes;
use RetailExpress\SkyLink\Model\Sales\Payments\InvoicePaymentHelper;

class WhenSkyLinkPaymentWasCreatedFromMagentoInvoice implements ObserverInterface
{
    use InvoiceExtensionAttributes;
    use InvoicePaymentHelper;

    public function __construct(
        ResourceConnection $resourceConnection,
        InvoiceExtensionFactory $invoiceExtensionFactory
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
    }

    public function execute(Observer $observer)
    {
        /* @var \Magento\Sales\Api\Data\InvoiceInterface $magentoInvoice */
        $magentoInvoice = $observer->getData('magento_invoice');

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\Payment $skyLinkPayment */
        $skyLinkPayment = $observer->getData('skylink_payment');

        // @todo Should we validae?
        $this->connection->insert(

            // @todo should this be centralised?
            $this->getInvoicesPaymentsTable(),
            [
                'magento_invoice_id' => $magentoInvoice->getEntityId(),
                'skylink_payment_id' => $skyLinkPayment->getId(),
            ]
        );

        /* @var \Magento\Sales\Api\Data\InvoiceExtensionInterfac $extendedAttributes */
        $extendedAttributes = $this->getInvoiceExtensionAttributes($magentoInvoice);
        $extendedAttributes->setSkylinkPaymentId($skyLinkPayment->getId());
    }
}
