<?php

namespace RetailExpress\SkyLink\Model\Debugging;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

trait LogHelper
{
    private $resourceConnection;

    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    private function getLogsTable()
    {
        return $this->getConnection()->getTableName('retail_express_skylink_logs');
    }

    private function getConnection()
    {
        if (null === $this->connection) {
            $resourceConnection = ObjectManager::getInstance()->get(ResourceConnection::class);
            $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        }

        return $this->connection;
    }
}
