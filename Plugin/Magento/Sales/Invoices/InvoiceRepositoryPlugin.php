<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Sales\Invoices;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use RetailExpress\SkyLink\Model\Sales\Invoices\InvoiceExtensionAttributes;
use RetailExpress\SkyLink\Model\Sales\Payments\InvoicePaymentHelper;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentId as SkyLinkPaymentId;

class InvoiceRepositoryPlugin
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

    public function afterGet(InvoiceRepositoryInterface $subject, InvoiceInterface $magentoInvoice)
    {
        $skyLinkPaymentIdString = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getInvoicesPaymentsTable(), 'skylink_payment_id')
                ->where('magento_invoice_id = ?', $magentoInvoice->getEntityId())
        );

        if (false !== $skyLinkPaymentIdString) {

            /* @var \Magento\Sales\Api\Data\InvoiceExtensionInterfac $extendedAttributes */
            $extendedAttributes = $this->getInvoiceExtensionAttributes($magentoInvoice);
            $extendedAttributes->setSkylinkPaymentId(new SkyLinkPaymentId($skyLinkPaymentIdString));
        }

        return $magentoInvoice;
    }
}
