<?php

namespace RetailExpress\SkyLink\Api\Segregation;

interface SalesChannelGroupRepositoryInterface
{
    /**
     * Gets a list of Sales Channel Groups.
     *
     * @return \RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface[]
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Segregation\SalesChannelIdMisconfiguredException
     */
    public function getList();
}
