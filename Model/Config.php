<?php

namespace RetailExpress\SkyLink\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;
use ValueObjects\Identity\UUID as Uuid;
use ValueObjects\Number\Integer;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class Config implements ConfigInterface
{
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the API version as configured globally.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getApiVersion()
    {
        return new Integer($this->scopeConfig->getValue('skylink/api/version'));
    }

    /**
     * Get the V2 API URL as configured globally.
     *
     * @return \ValueObjects\Web\Url\Url
     */
    public function getV2ApiUrl()
    {
        return Url::fromNative((string) $this->scopeConfig->getValue('skylink/api/version_2_url'));
    }

    /**
     * Get the V2 API Client ID as configured globally.
     *
     * @return Uuid
     */
    public function getV2ApiClientId()
    {
        return Uuid::fromNative((string) $this->scopeConfig->getValue('skylink/api/version_2_client_id'));
    }

    /**
     * Get the V2 API Username as configured globally.
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getV2ApiUsername()
    {
        return new StringLiteral((string) $this->scopeConfig->getValue('skylink/api/version_2_username'));
    }

    /**
     * Get the V2 API Password as configured globally.
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getV2ApiPassword()
    {
        return new StringLiteral((string) $this->scopeConfig->getValue('skylink/api/version_2_password'));
    }

    /**
     * Get the Sales Channel ID as configured for the current active website.
     *
     * @return \RetailExpress\SkyLink\ValueObjects\SalesChannelId
     */
    public function getSalesChannelId()
    {
        return new SalesChannelId($this->scopeConfig->getValue('skylink/general/sales_channel_id'));
    }
}
