<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;
use RetailExpress\SkyLink\Commands\BatchCommand;

class SyncSkyLinkCustomerToMagentoCustomerCommand extends NormallyQueuedCommand implements QueueableCommand
{
    use BatchCommand;

    /**
     * The SkyLink Customer ID.
     *
     * @var int
     */
    public $skyLinkCustomerId;

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
        return 'sync_skylink_to_magento';
    }
}
