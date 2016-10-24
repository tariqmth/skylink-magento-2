<?php

namespace RetailExpress\SkyLink\Catalogue\Attributes;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Apis\V2Factory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;

class AttributeRepositoryFactory
{
    use Factory;

    private $config;

    private $v2ApiFactory;

    public function __construct(
        ConfigInterface $config,
        V2ApiFactory $v2ApiFactory
    ) {
        $this->config = $config;
        $this->v2ApiFactory = $v2ApiFactory;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        return new V2AttributeRepository($this->v2ApiFactory->create());
    }
}
