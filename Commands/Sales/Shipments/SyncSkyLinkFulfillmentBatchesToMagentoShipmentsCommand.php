<?php

namespace RetailExpress\SkyLink\Commands\Sales\Shipments;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand extends NormallyQueuedCommand implements QueueableCommand
{
    public $skyLinkOrderId;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return 'orders';
    }

    /**
     * Get the name of the command on the queue.
     *
     * @return string
     */
    public function getName()
    {
        return 'sync_skylink_fulfillment_batches_to_magento_shipments';
    }
}
