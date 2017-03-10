<?php

namespace RetailExpress\SkyLink\Eds;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Apis\V2Factory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;

class ChangeSetDeserialiserFactory
{
    use Factory;

    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        return new V2ChangeSetDeserialiser();
    }
}
