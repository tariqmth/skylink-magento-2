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
    public function canShow()
    {
        // We need to show out of stock products as well as product status' to care if we're enabled
        return $this->isEnabled() &&
            $this->isDisplayingProductStockStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonTitle()
    {
        return $this->scopeConfig->getValue('cataloginventory/options/eta_button_title');
    }

    /**
     * {@inheritdoc}
     */
    public function getDisclaimerLabel()
    {
        return $this->scopeConfig->getValue('cataloginventory/options/eta_disclaimer_label');
    }

    /**
     * {@inheritdoc}
     */
    public function getNoDateLabel()
    {
        return $this->scopeConfig->getValue('cataloginventory/options/eta_no_date_label');
    }

    /**
     * {@inheritdoc}
     */
    public function shouldReplaceProductStockStatus()
    {
        return 1 == $this->scopeConfig->getValue('cataloginventory/options/eta_replace_product_stock_status');
    }

    /**
     * {@inheritdoc}
     */
    public function getProductStockStatusLabel()
    {
        return $this->scopeConfig->getValue('cataloginventory/options/eta_replace_product_stock_status_label');
    }

    /**
     * Determine if ETA is enabled.
     *
     * @return bool
     */
    private function isEnabled()
    {
        return 1 == $this->scopeConfig->getValue('cataloginventory/options/eta_enabled');
    }

    /**
     * Determine if product stock status is displayed on the frontend.
     *
     * @return bool
     */
    private function isDisplayingProductStockStatus()
    {
        return 1 == $this->scopeConfig->getValue('cataloginventory/options/display_product_stock_status');
    }
}
