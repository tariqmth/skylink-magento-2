<?php

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use RetailExpress\SkyLink\Api\Catalogue\Eta\ConfigInterface as EtaConfig;
use RetailExpress\SkyLink\Api\Catalogue\Eta\HelperInterface as EtaHelper;

if (!function_exists('eta_replaces_stock_status')) {

    /**
     * Determine if ETA replaces stock status for the given product.
     *
     * @param ProductInterface $magentoProduct
     *
     * @return bool
     */
    function eta_replaces_stock_status(ProductInterface $magentoProduct)
    {
        return ObjectManager::getInstance()->get(EtaHelper::class)->canShow($magentoProduct) &&
            ObjectManager::getInstance()->get(EtaConfig::class)->shouldReplaceProductStockStatus();
    }

}

if (!function_exists('eta_stock_status_label')) {

    /**
     * Get the ETA product stock status label.
     *
     * @return string
     */
    function eta_stock_status_label()
    {
        return ObjectManager::getInstance()->get(EtaConfig::class)->getProductStockStatusLabel();
    }

}
