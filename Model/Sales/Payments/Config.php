<?php

namespace RetailExpress\SkyLink\Model\Sales\Payments;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\ConfigInterface;
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
    public function getCacheTime()
    {
        return new Integer($this->scopeConfig->getValue('skylink/payments/cache_time'));
    }
}
