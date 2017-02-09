<?php

namespace RetailExpress\SkyLink\Sdk\Sales\Orders;

use Magento\Framework\App\Filesystem\DirectoryList;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\ConfigInterface as OrderConfigInterface;
use RetailExpress\SkyLink\Sdk\Apis\V2Factory as V2ApiFactory;
use RetailExpress\SkyLink\Model\Factory;
use RetailExpress\SkyLink\Sdk\V2OrderShim\DefaultBulkOrderCacher;
use RetailExpress\SkyLink\Sdk\V2OrderShim\OrderRepository;
use RetailExpress\SkyLink\Sdk\V2OrderShim\Storage\FilesystemStorage;
use ValueObjects\StringLiteral\StringLiteral;

class OrderRepositoryFactory
{
    use Factory;

    private $config;

    private $v2ApiFactory;

    private $directoryList;

    private $orderConfig;

    public function __construct(
        ConfigInterface $config,
        V2ApiFactory $v2ApiFactory,
        DirectoryList $directoryList,
        OrderConfigInterface $orderConfig
    ) {
        $this->config = $config;
        $this->v2ApiFactory = $v2ApiFactory;
        $this->directoryList = $directoryList;
        $this->orderConfig = $orderConfig;
    }

    public function create()
    {
        $this->assertV2Api($this->config->getApiVersion());

        $api = $this->v2ApiFactory->create();

        return new V2OrderRepository($api);
    }
}
