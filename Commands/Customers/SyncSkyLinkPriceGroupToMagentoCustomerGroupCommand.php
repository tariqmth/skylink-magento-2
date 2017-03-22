<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\CommandBus\Api\Queues\AlwaysQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand extends AlwaysQueuedCommand implements QueueableCommand
{
    use EdsPriceGroupIdWorkaround;

    /**
     * The SkyLink Price Group Key.
     *
     * @var string
     */
    public $skyLinkPriceGroupKey;

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
