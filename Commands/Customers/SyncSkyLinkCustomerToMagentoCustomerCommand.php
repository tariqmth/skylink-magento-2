<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class SyncSkyLinkCustomerToMagentoCustomerCommand extends NormallyQueuedCommand implements QueueableCommand
{
    /**
     * The SkyLink Customer ID.
     *
     * @var int
     */
    public $skyLinkCustomerId;

    /**
     * An optional EDS Change Set ID that this command is associated with.
     *
     * @var string
     */
    public $changeSetId;

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
