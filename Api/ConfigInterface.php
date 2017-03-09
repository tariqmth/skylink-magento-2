<?php

namespace RetailExpress\SkyLink\Api;

interface ConfigInterface
{
    /**
     * Get the API version as configured globally.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getApiVersion();

    /**
     * Get the V2 API URL as configured globally.
     *
     * @return \ValueObjects\Web\Url\Url
     */
    public function getV2ApiUrl();

    /**
     * Get the V2 API Client ID as configured globally.
     *
     * @return \ValueObjects\Identity\UUID
     */
    public function getV2ApiClientId();

    /**
     * Get the V2 API Username as configured globally.
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getV2ApiUsername();

    /**
     * Get the V2 API Password as configured globally.
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getV2ApiPassword();

    /**
     * Get the Sales Channel ID as configured globally.
     *
     * @return \RetailExpress\SkyLink\ValueObjects\SalesChannelId
     *
     * @throws \RetailExpress\SkyLink\Exceptions\NoSalesChannelIdConfiguredException
     */
    public function getSalesChannelId();

    /**
     * Get the Sales Channel ID as configured for the given website.
     *
     * @param string $websiteCode
     *
     * @return \RetailExpress\SkyLink\ValueObjects\SalesChannelId
     *
     * @throws \RetailExpress\SkyLink\Exceptions\SalesChannelIdMisconfiguredException
     */
    public function getSalesChannelIdForWebsite($websiteCode);
}
