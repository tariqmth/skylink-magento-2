<?php

namespace RetailExpress\SkyLinkMagento2\Model;

use RetailExpress\SkyLink\Apis\V2 as V2Api;
use ValueObjects\Number\Integer;

/**
 * @todo Add DI - it isn't playing nicely with value objects so just hardcoding instead.
 */
class ApiFactory
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Assert a valid, supported API version is configured and run the provided callback against that API version.
     *
     * @return mixed
     */
    public function assertValidApiVersion()
    {
        if (!$this->isV2Api()) {
            throw new InvalidArgumentException('Only supported version of the Retail Express API is the V2 API.');
        }
    }

    /**
     * Determine if the current API version is the V2 API.
     *
     * @return bool
     */
    public function isV2Api()
    {
        return $this->config->getApiVersion()->sameValueAs(new Integer(2));
    }

    /**
     * Create a V2 API object that is used throughout V2 API repositories.
     *
     * @return V2Api
     */
    public function createV2Api()
    {
        return new V2Api(
            $this->config->getV2ApiUrl(),
            $this->config->getV2ApiClientId(),
            $this->config->getV2ApiUsername(),
            $this->config->getV2ApiPassword()
        );
    }
}
