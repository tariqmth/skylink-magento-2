<?php

namespace RetailExpress\SkyLink\Api\Sales\Payments;

use Magento\Sales\Api\Data\InvoiceInterface;

interface SkyLinkPaymentBuilderInterface
{
    /**
     * Builds a SkyLink Payment to correlate to the given Magento Invoice.
     *
     * @param InvoiceInterface $magentoInvoice
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Payments\Payment
     */
    public function buildFromMagentoInvoice(InvoiceInterface $magentoInvoice);
}
