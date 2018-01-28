<?php

namespace RetailExpress\SkyLink\Commands\Sales\Payments;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;
use RetailExpress\SkyLink\Commands\BatchCommand;

class SyncSkyLinkPaymentToMagentoPaymentCommand extends NormallyQueuedCommand implements QueueableCommand
{
    use BatchCommand;

    /**
     * The SkyLink Order ID.
     *
     * @var string
     */
    public $skyLinkOrderId;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return 'fulfillments';
    }

    /**
     * Get the name of the command on the queue.
     *
     * @return string
     */
    public function getName()
    {
        return 'sync_skylink_payment_to_magento_payment';
    }
}
