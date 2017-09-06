<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Framework\App\CacheInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSyncCompositeProductRerunManagerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\CompositeProduct as CompositeSkyLinkProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoSyncCompositeProductRerunManager implements MagentoSyncCompositeProductRerunManagerInterface
{
    const CACHE_KEY = 'retail_express_skylink_composite_product_rerun';

    private $config;

    private $cache;

    public function __construct(ConfigInterface $config, CacheInterface $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function canProceedWithSync(CompositeSkyLinkProduct $skyLinkCompositeProduct, array $additional = [])
    {
        $cacheKey = $this->getCacheKey($skyLinkCompositeProduct, $additional);

        return !(bool) $this->cache->load($cacheKey);
    }

    /**
     * {@inheritdoc}
     */
    public function isSyncing(CompositeSkyLinkProduct $skyLinkCompositeProduct, array $additional = [])
    {
        $cacheKey = $this->getCacheKey($skyLinkCompositeProduct, $additional);

        $this->cache->save(true, $cacheKey, [], $this->config->getCompositeProductSyncRerunThreshold()->toNative());
    }

    private function getCacheKey(CompositeSkyLinkProduct $skyLinkCompositeProduct, array $additional = [])
    {
        $simpleProductIds = array_map(function (SkyLinkProduct $skyLinkProduct) {
            return (string) $skyLinkProduct->getId();
        }, $skyLinkCompositeProduct->getProducts());

        sort($simpleProductIds);

        return md5(sprintf(
            '%s_%s_%s_%s',
            self::CACHE_KEY,
            $skyLinkCompositeProduct->getSku(),
            implode('_', $simpleProductIds),
            implode('_', $additional)
        ));
    }
}
