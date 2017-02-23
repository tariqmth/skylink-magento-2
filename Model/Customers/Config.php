<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;

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

    /**
     * {@inheritdoc}
     */
    public function getSkyLinkPriceGroupType()
    {
        return SkyLinkPriceGroupType::get(
            $this->scopeConfig->getValue('skylink/customers/price_group_type')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCustomerGroupId()
    {
        return $this->scopeConfig->getValue(GroupManagement::XML_PATH_DEFAULT_ID);
    }
}
