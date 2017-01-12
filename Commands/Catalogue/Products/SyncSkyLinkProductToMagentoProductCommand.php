<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class SyncSkyLinkProductToMagentoProductCommand extends NormallyQueuedCommand implements QueueableCommand
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
     * An optional EDS Change Set ID that this command is associated with.
     *
     * @var string
     */
    public $changeSetId;

    /**
     * Flag for whether the sync is stock only.
     *
     * Note: This requires a corresponding product to exist in Magento.
     *
     * @var bool
     */
    public $stockOnly = false;

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
