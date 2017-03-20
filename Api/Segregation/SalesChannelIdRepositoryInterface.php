<?php

namespace RetailExpress\SkyLink\Api\Segregation;

use RetailExpress\SkyLink\Api\Segregation\SalesChannelIdRepositoryInterface;

interface SalesChannelIdRepositoryInterface
{
    /**
     * @return \RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId
     */
    public function getSalesChannelIdForCurrentWebsite();
}
