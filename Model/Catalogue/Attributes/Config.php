<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\ConfigInterface;
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
        return new Integer($this->scopeConfig->getValue('skylink/attributes/cache_time'));
    }
}
