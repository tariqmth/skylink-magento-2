<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Attributes;

use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class SyncSkyLinkAttributeToMagentoAttributeCommand implements QueueableCommand
{
    /**
     * The Magento Attribute Code.
     *
     * @var string
     */
    public $magentoAttributeCode;

    /**
     * The SkyLink Attribute Code.
     *
     * @var string
     */
    public $skyLinkAttributeCode;

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
