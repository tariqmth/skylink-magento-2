<?php

namespace RetailExpress\SkyLink\Sdk\Apis;

use EcomDev\CacheKey\GeneratorInterface as CacheKeyGeneratorInterface;
use Psr\Cache\CacheItemPoolInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;

class V2Factory
{
    private $config;

    private $cache;

    private $cacheKeyGenerator;

    public function __construct(
        ConfigInterface $config,
        CacheItemPoolInterface $cache,
        CacheKeyGeneratorInterface $cacheKeyGenerator
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
    }

    public function create()
    {
        $v2 = new V2(
            $this->config->getV2ApiUrl(),
            $this->config->getV2ApiClientId(),
            $this->config->getV2ApiUsername(),
            $this->config->getV2ApiPassword()
        );

        $v2->useCache($this->cache, $this->cacheKeyGenerator);

        return $v2;
    }
}
