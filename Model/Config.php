<?php

namespace RetailExpress\SkyLink\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Exceptions\SalesChannelIdMisconfiguredException;
use RetailExpress\SkyLink\Exceptions\NoSalesChannelIdConfiguredException;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;
use ValueObjects\Identity\UUID as Uuid;
use ValueObjects\Number\Integer;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class Config implements ConfigInterface
{
    const SALES_CHANNEL_ID_CONFIG_PATH = 'skylink/general/sales_channel_id';

    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiVersion()
    {
        return new Integer($this->scopeConfig->getValue('skylink/api/version'));
    }

    /**
     * {@inheritdoc}
     */
    public function getV2ApiUrl()
    {
        return Url::fromNative((string) $this->scopeConfig->getValue('skylink/api/version_2_url'));
    }

    /**
     * {@inheritdoc}
     */
    public function getV2ApiClientId()
    {
        return Uuid::fromNative((string) $this->scopeConfig->getValue('skylink/api/version_2_client_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function getV2ApiUsername()
    {
        return new StringLiteral((string) $this->scopeConfig->getValue('skylink/api/version_2_username'));
    }

    /**
     * {@inheritdoc}
     */
    public function getV2ApiPassword()
    {
        return new StringLiteral((string) $this->scopeConfig->getValue('skylink/api/version_2_password'));
    }

    /**
     * {@inheritdoc}
     */
    public function getSalesChannelId()
    {
        $value = $this->scopeConfig->getValue(self::SALES_CHANNEL_ID_CONFIG_PATH);

        if (!is_numeric($value)) {
            throw NoSalesChannelIdConfiguredException::forGlobalScope();
        }

        return new SalesChannelId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSalesChannelIdForWebsite($websiteCode)
    {
        $value = $this->scopeConfig->getValue(self::SALES_CHANNEL_ID_CONFIG_PATH, 'website', $websiteCode);

        if (!is_numeric($value)) {
            throw SalesChannelIdMisconfiguredException::forWebsiteWithConfigValue($websiteCode, $value);
        }

        return new SalesChannelId($value);
    }
}
