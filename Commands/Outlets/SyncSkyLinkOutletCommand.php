<?php

namespace RetailExpress\SkyLink\Commands\Outlets;

use RetailExpress\CommandBus\Api\Queues\AlwaysQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class SyncSkyLinkOutletCommand extends AlwaysQueuedCommand implements QueueableCommand
{
    public $skyLinkOutletId;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getQueue()
    {
        return 'outlets';
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
        return 'sync';
    }
}
