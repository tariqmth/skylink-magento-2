<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Attributes;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;
use RetailExpress\SkyLink\Commands\Eds\ChangeSetCommand;

class SyncSkyLinkAttributeToMagentoAttributeCommand extends NormallyQueuedCommand implements QueueableCommand
{
    use ChangeSetCommand;

    use EdsAttributeOptionIdWorkaround;

    /**
     * The SkyLink Attribute Code.
     *
     * @var string
     */
    public $skyLinkAttributeCode;

    /**
     * The Magento Attribute Code.
     *
     * @var string
     */
    public $magentoAttributeCode;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return 'attributes';
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
