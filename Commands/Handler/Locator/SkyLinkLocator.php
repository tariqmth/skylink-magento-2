<?php

namespace RetailExpress\SkyLinkMagento2\Commands\Handler\Locator;

use League\Tactician\Handler\Locator\HandlerLocator;
use Magento\Framework\ObjectManagerInterface;

class SkyLinkLocator implements HandlerLocator
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getHandlerForCommand($commandName)
    {
        // Strip "Command" and add "Handler"
        return $this->objectManager->create(substr($commandName, 0, -7).'Handler');
    }
}
