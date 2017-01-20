<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class SyncMagentoCustomerToSkyLinkCustomerCommand extends NormallyQueuedCommand implements QueueableCommand
{
    /**
     * The Magento Customer ID.
     *
     * @var int
     */
    public $magentoCustomerId;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getQueue()
    {
        return 'customers';
    }

    /**
     * Get the name of the command on the queue.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'sync_magento_to_skylink';
    }
}
