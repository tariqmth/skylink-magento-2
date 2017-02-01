<?php

namespace RetailExpress\SkyLink\Sdk\Catalogue\Products;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Apis\V2Factory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;

class ProductRepositoryFactory
{
    use Factory;

    private $config;

    private $v2ApiFactory;

    private $matrixPolicyMapperFactory;

    public function __construct(
        ConfigInterface $config,
        MatrixPolicyMapperFactory $matrixPolicyMapperFactory,
        V2ApiFactory $v2ApiFactory
    ) {
        $this->config = $config;
        $this->matrixPolicyMapperFactory = $matrixPolicyMapperFactory;
        $this->v2ApiFactory = $v2ApiFactory;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        /* @var MatrixPolicyMapper $matrixPolicyMapper */
        $matrixPolicyMapper = $this->matrixPolicyMapperFactory->create();

        $deserializer = new V2ProductDeserializer();

        return new V2ProductRepository($matrixPolicyMapper, $deserializer, $this->v2ApiFactory->create());
    }
}
