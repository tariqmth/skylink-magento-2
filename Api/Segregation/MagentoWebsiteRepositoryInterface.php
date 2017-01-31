<?php

namespace RetailExpress\SkyLink\Api\Segregation;

interface MagentoWebsiteRepositoryInterface
{
    /**
     * Gets a list of all Magento Websites that will be associated with any data segregation.
     *
     * @return \RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface[]
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Segregation\SalesChannelIdMisconfiguredException
     */
    public function getList();
}
