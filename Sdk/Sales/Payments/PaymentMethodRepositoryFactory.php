<?php

namespace RetailExpress\SkyLink\Sdk\Sales\Payments;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\ConfigInterface as PaymentConfigInterface;
use RetailExpress\SkyLink\Sdk\Apis\V2Factory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;

class PaymentMethodRepositoryFactory
{
    use Factory;

    private $config;

    private $paymentConfig;

    private $v2ApiFactory;

    public function __construct(
        ConfigInterface $config,
        PaymentConfigInterface $paymentConfig,
        V2ApiFactory $v2ApiFactory
    ) {
        $this->config = $config;
        $this->paymentConfig = $paymentConfig;
        $this->v2ApiFactory = $v2ApiFactory;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        return new V2PaymentMethodRepository(
            $this->v2ApiFactory->create(),
            $this->paymentConfig->getCacheTime()
        );
    }
}
