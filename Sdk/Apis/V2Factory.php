<?php

namespace RetailExpress\SkyLink\Sdk\Apis;

use Magento\Framework\ObjectManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;

class V2Factory
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
        return $this->objectManager->create(V2::class, [
            'url' => $this->config->getV2ApiUrl(),
            'clientId' => $this->config->getV2ApiClientId(),
            'username' => $this->config->getV2ApiUsername(),
            'password' => $this->config->getV2ApiPassword()
        ]);
    }
}
