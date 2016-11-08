<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;

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
    public function getConfigurableProductMatchThreshold()
    {
        return new ConfigurableProductMatchThreshold(
            $this->scopeConfig->getValue('skylink/products/configurable_product_match_threshold')
        );
    }
}
