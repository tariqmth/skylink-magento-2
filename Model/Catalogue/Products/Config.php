<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductNameAttribute as SkyLinkProductNameAttribute;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductPriceAttribute as SkyLinkProductPriceAttribute;
use ValueObjects\Number\Integer;

class Config implements ConfigInterface
{
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameAttribute()
    {
        return SkyLinkProductNameAttribute::get(
            $this->scopeConfig->getValue('skylink/products/name_attribute')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPriceAttribute()
    {
        return SkyLinkProductPriceAttribute::get(
            $this->scopeConfig->getValue('skylink/products/regular_price_attribute')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecialPriceAttribute()
    {
        return SkyLinkProductPriceAttribute::get(
            $this->scopeConfig->getValue('skylink/products/special_price_attribute')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableProductMatchThreshold()
    {
        return new ConfigurableProductMatchThreshold(
            $this->scopeConfig->getValue('skylink/products/configurable_product_match_threshold')
        );
    }

    public function getCompositeProductSyncRerunThreshold()
    {
        return new Integer(
            $this->scopeConfig->getValue('skylink/products/composite_product_sync_rerun_threshold')
        );
    }
}
