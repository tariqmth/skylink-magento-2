<?php

namespace RetailExpress\SkyLink\Model\Sales\Payments;

trait SkyLinkPaymentMethodHelpers
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getPaymentMethodsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_payment_methods');
    }
}
