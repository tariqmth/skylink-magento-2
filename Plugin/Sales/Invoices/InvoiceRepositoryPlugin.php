<?php

namespace RetailExpress\SkyLink\Plugin\Sales\Invoices;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class InvoiceRepositoryPlugin
{
    public function afterGet(InvoiceRepositoryInterface $subject, InvoiceInterface $magentoInvoice)
    {
        // Retrieve mapping if it exists

        return $magentoInvoice;
    }

    public function afterSave(InvoiceRepositoryInterface $subject, InvoiceInterface $magentoInvoice)
    {
        // Save mapping if it does not exist

        return $magentoInvoice;
    }
}
