<?php

namespace RetailExpress\SkyLink\Model\Sales\Payments;

trait InvoicePaymentHelper
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getInvoicesPaymentsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_invoices_payments');
    }
}
