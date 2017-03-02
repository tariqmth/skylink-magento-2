<?php

namespace RetailExpress\SkyLink\Sdk\Catalogue\Products;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface as ProductConfigInterface;
use RetailExpress\SkyLink\Sdk\Apis\V2Factory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;

class ProductRepositoryFactory
{
    use Factory;

    private $config;

    private $productConfig;

    private $v2ApiFactory;

    private $matrixPolicyMapperFactory;

    public function __construct(
        ConfigInterface $config,
        ProductConfigInterface $productConfig,
        MatrixPolicyMapperFactory $matrixPolicyMapperFactory,
        V2ApiFactory $v2ApiFactory
    ) {
        $this->config = $config;
        $this->productConfig = $productConfig;
        $this->matrixPolicyMapperFactory = $matrixPolicyMapperFactory;
        $this->v2ApiFactory = $v2ApiFactory;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicyMapper $matrixPolicyMapper */
        $matrixPolicyMapper = $this->matrixPolicyMapperFactory->create();

        $deserializer = new V2ProductDeserializer(
            $this->productConfig->getNameAttribute(),
            $this->productConfig->getRegularPriceAttribute(),
            $this->productConfig->getSpecialPriceAttribute()
        );

        return new V2ProductRepository($matrixPolicyMapper, $deserializer, $this->v2ApiFactory->create());
    }
}
