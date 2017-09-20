<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;
use RetailExpress\SkyLink\Commands\BatchCommand;

class SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand extends NormallyQueuedCommand implements QueueableCommand
{
    use BatchCommand;

    /**
     * The SkyLink Price Group Key.
     *
     * @var string
     */
    public $skyLinkPriceGroupKey;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getQueue()
    {
        return 'price-groups';
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
