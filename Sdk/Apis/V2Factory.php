<?php

namespace RetailExpress\Sdk\SkyLink\Apis;

use RetailExpress\SkyLink\Api\ConfigInterface;

class V2Factory
{
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        return new V2(
            $this->config->getV2ApiUrl(),
            $this->config->getV2ApiClientId(),
            $this->config->getV2ApiUsername(),
            $this->config->getV2ApiPassword()
        );
    }
}
