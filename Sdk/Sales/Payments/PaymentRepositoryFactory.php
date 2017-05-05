<?php

namespace RetailExpress\SkyLink\Sdk\Sales\Payments;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Model\Factory;
use RetailExpress\SkyLink\Sdk\Apis\V2\ApiFactory as V2ApiFactory;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepositoryFactory;

class PaymentRepositoryFactory
{
    use Factory;

    private $config;

    private $v2ApiFactory;

    private $orderRepositoryFactory;

    public function __construct(
        ConfigInterface $config,
        V2ApiFactory $v2ApiFactory,
        OrderRepositoryFactory $orderRepositoryFactory
    ) {
        $this->config = $config;
        $this->v2ApiFactory = $v2ApiFactory;
        $this->orderRepositoryFactory = $orderRepositoryFactory;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        return new V2PaymentRepository(
            $this->v2ApiFactory->create(),
            $this->orderRepositoryFactory->create()
        );
    }
}
