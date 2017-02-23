<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;

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
    public function getCustomerGroupTaxClassId()
    {
        return (int) $this->scopeConfig->getValue('skylink/customers/customer_group_tax_class_id');
    }
}
