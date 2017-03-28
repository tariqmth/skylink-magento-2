<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;
use ValueObjects\Geography\CountryCode;
use ValueObjects\StringLiteral\StringLiteral;

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
        return (int) $this->scopeConfig->getValue(GroupManagement::XML_PATH_DEFAULT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function shouldUseFakeData()
    {
        return (bool) $this->scopeConfig->getValue('skylink/customers/use_fake_data');
    }

    /**
     * {@inheritdoc}
     */
    public function getFakeDataFirstName()
    {
        return new StringLiteral($this->scopeConfig->getValue('skylink/customers/fake_data_first_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFakeDataLastName()
    {
        return new StringLiteral($this->scopeConfig->getValue('skylink/customers/fake_data_last_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFakeDataStreet()
    {
        return new StringLiteral($this->scopeConfig->getValue('skylink/customers/fake_data_street'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFakeDataCity()
    {
        return new StringLiteral($this->scopeConfig->getValue('skylink/customers/fake_data_city'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFakeDataPostcode()
    {
        return new StringLiteral($this->scopeConfig->getValue('skylink/customers/fake_data_postcode'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFakeDataCountryCode()
    {
        return CountryCode::fromNative($this->scopeConfig->getValue('skylink/customers/fake_data_country_code'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFakeDataTelephone()
    {
        return new StringLiteral($this->scopeConfig->getValue('skylink/customers/fake_data_telephone'));
    }
}
