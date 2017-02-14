<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Eta;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Eta\ConfigInterface;

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
    public function canUse()
    {
        // We need to show out of stock products as well as product status' to care if we're enabled
        return $this->outOfStockAreShown() &&
            $this->isDisplayingProductStockStatus() &&
            $this->isEnabled();
    }

    private function isEnabled()
    {
        return 1 == $this->scopeConfig->getValue('cataloginventory/options/eta');
    }

    private function outOfStockAreShown()
    {
        return 1 == $this->scopeConfig->getValue('cataloginventory/options/show_out_of_stock');
    }

    private function isDisplayingProductStockStatus()
    {
        return 1 == $this->scopeConfig->getValue('cataloginventory/options/display_product_stock_status');
    }
}
