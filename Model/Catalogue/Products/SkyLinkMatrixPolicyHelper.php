<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

trait SkyLinkMatrixPolicyHelper
{
    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getMatrixPoliciesTable()
    {
        return $this->connection->getTableName('retail_express_skylink_matrix_policies');
    }
}
