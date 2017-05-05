<?php

namespace RetailExpress\SkyLink\Sdk\Apis\V2;

use Magento\Framework\ObjectManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;

class ApiFactory
{
    private $config;

    private $objectManager;

    public function __construct(ConfigInterface $config, ObjectManagerInterface $objectManager)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    public function create()
    {
        $api = new Api(
            $this->config->getV2ApiUrl(),
            $this->config->getV2ApiClientId(),
            $this->config->getV2ApiUsername(),
            $this->config->getV2ApiPassword()
        );

        $api->addMiddlewareAfter(
            $this->objectManager->create(LoggingMiddleware::class),
            InvalidClientIdMiddleware::class
        );

        return $api;
    }
}
