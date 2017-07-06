<?php

namespace RetailExpress\SkyLink\Observer\Catalogue\Eta;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Api\Catalogue\Eta\ConfigInterface;

class WhenLayoutIsLoading implements ObserverInterface
{
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        if ($this->config->canShow() && $this->config->shouldReplaceProductStockStatus()) {
            $observer->getData('layout')->getUpdate()->addHandle('skylink_catalogue_eta_product_stock_status');
        }
    }
}
