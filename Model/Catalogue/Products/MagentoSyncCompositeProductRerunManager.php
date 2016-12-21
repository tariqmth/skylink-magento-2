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
    public function canProceedWithSync(CompositeSkyLinkProduct $skyLinkCompositeProduct)
    {
        $cacheKey = $this->getCacheKey($skyLinkCompositeProduct);

        return !(bool) $this->cache->load($cacheKey);
    }

    /**
     * {@inheritdoc}
     */
    public function didSync(CompositeSkyLinkProduct $skyLinkCompositeProduct)
    {
        $cacheKey = $this->getCacheKey($skyLinkCompositeProduct);

        $this->cache->save(true, $cacheKey, [], $this->config->getCompositeProductSyncRerunThreshold()->toNative());
    }

    private function getCacheKey(CompositeSkyLinkProduct $skyLinkCompositeProduct)
    {
        $simpleProductIds = array_map(function (SkyLinkProduct $skyLinkProduct) {
            return (string) $skyLinkProduct->getId();
        }, $skyLinkCompositeProduct->getProducts());

        return sprintf(
            '%s_%s',
            self::CACHE_KEY,
            md5(implode('', $simpleProductIds))
        );
    }
}
