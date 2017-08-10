<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Model\Catalogue\Products\QuantityCalculation;
use RetailExpress\SkyLink\Model\Catalogue\SyncStrategy;
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

    public function getNameSyncStrategy()
    {
        return SyncStrategy::get(
            $this->scopeConfig->getValue('skylink/products/name_sync_strategy')
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
    public function getQuantityCalculation()
    {
        return QuantityCalculation::get(
            $this->scopeConfig->getValue('skylink/products/quantity_calculation')
        );
    }

    public function getUrlKeyAttributeCodes()
    {
        $values = [];

        for ($i = 1; $i <= 3; $i++) {
            $value = $this->scopeConfig->getValue("skylink/products/url_key_attribute_code_{$i}");

            if ('0' === $value) {
                continue;
            }

            $values[] = $value;
        }

        return $values;
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

    /**
     * {@inheritdoc}
     */
    public function getProductTypesForSimpleProductSync()
    {
        return array_map('trim',
            explode(',', $this->scopeConfig->getValue('skylink/products/product_types_for_simple_product_sync'))
        );
    }
}
