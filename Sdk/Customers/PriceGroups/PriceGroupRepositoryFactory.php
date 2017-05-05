<?php

namespace RetailExpress\SkyLink\Sdk\Customers\PriceGroups;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Apis\V2\ApiFactory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;

class PriceGroupRepositoryFactory
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

        return new V2PriceGroupRepository($this->v2ApiFactory->create());
    }
}
