<?php

namespace RetailExpress\SkyLink\Sdk\Catalogue\Attributes;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\ConfigInterface as AttributeConfigInterface;
use RetailExpress\SkyLink\Sdk\Apis\V2Factory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;

class AttributeRepositoryFactory
{
    use Factory;

    private $config;

    private $attributeConfig;

    private $v2ApiFactory;

    public function __construct(
        ConfigInterface $config,
        AttributeConfigInterface $attributeConfig,
        V2ApiFactory $v2ApiFactory
    ) {
        $this->config = $config;
        $this->attributeConfig = $attributeConfig;
        $this->v2ApiFactory = $v2ApiFactory;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        return new V2AttributeRepository(
            $this->v2ApiFactory->create(),
            $this->attributeConfig->getCacheTime()
        );
    }
}
