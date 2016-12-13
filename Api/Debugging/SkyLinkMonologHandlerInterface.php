<?php

namespace RetailExpress\SkyLink\Api\Debugging;

use Monolog\Handler\HandlerInterface;

interface SkyLinkMonologHandlerInterface extends HandlerInterface
{
    const CONTEXT_KEY = 'skylink';
}
