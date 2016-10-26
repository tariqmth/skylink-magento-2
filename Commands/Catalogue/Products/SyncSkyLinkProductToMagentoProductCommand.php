<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use RetailExpress\CommandBus\Queues\QueueableCommand;

class SyncSkyLinkProductToMagentoProductCommand implements QueueableCommand
{
    /**
     * The SkyLink Product ID.
     *
     * @var int
     */
    public $skyLinkProductId;

    /**
     * The Sales Channel ID.
     *
     * @var int
     */
    public $salesChannelId;

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
