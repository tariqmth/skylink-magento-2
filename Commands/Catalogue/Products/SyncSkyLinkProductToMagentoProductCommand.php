<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;
use RetailExpress\SkyLink\Commands\BatchCommand;

class SyncSkyLinkProductToMagentoProductCommand extends NormallyQueuedCommand implements QueueableCommand
{
    use BatchCommand;

    /**
     * The SkyLink Product ID.
     *
     * @var int
     */
    public $skyLinkProductId;

    /**
     * Flag for whether the sync is a potential composite product rerun (meaning that
     * there might be a request to sync multiple child products from the same
     * product group [which currently consists of a matrix]). Clever use of
     * timing will asist in skipping subsequent syncs for the same matrix.
     *
     * @var bool
     */
    public $potentialCompositeProductRerun = false;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return 'products';
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
