<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\ConfigInterface;
use RetailExpress\SkyLink\Sdk\V2OrderShim\RecacheThreshold;

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
    public function getBulkOrderRecacheThreshold()
    {
        return new RecacheThreshold(
            $this->scopeConfig->getValue('skylink/orders/bulk_order_recache_threshold')
        );
    }
}
