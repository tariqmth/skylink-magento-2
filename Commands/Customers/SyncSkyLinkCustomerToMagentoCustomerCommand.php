<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\CommandBus\Queues\QueueableCommand;

class SyncSkyLinkCustomerToMagentoCustomerCommand /*implements QueueableCommand*/
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
     */
    public function getQueue()
    {
        return 'customers';
    }

    /**
     * Get the name of the command on the queue.
     *
     * @return string
     */
    public function getName()
    {
        return 'sync_skylink_to_magento';
    }
}
