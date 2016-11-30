<?php

namespace RetailExpress\SkyLink\Model\Sales\Invoices;

use Magento\Sales\Api\Data\InvoiceInterface;

trait InvoiceExtensionAttributes
{
    /**
     * Invoice Extnesion Factory
     *
     * @var \Magento\Sales\Api\Data\InvoiceExtensionFactory
     */
    private $invoiceExtensionFactory;

    /**
     * @return \Magento\Sales\Api\Data\InvoiceExtensionInterfac
     */
    private function getInvoiceExtensionAttributes(InvoiceInterface $magentoInvoice)
    {
        $extendedAttributes = $magentoInvoice->getExtensionAttributes();

        if (null === $extendedAttributes) {

            /* @var \Magento\Sales\Api\Data\InvoiceExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->invoiceExtensionFactory->create();
            $magentoInvoice->setExtensionAttributes($extendedAttributes);
        }

        return $extendedAttributes;
    }
}
