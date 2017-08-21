<?php

namespace RetailExpress\SkyLink\Api\Segregation;

interface MagentoWebsiteRepositoryInterface
{
    /**
     * Gets a list of all Magento Websites that use the same Sales Channel ID as the globally configured one.
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getListFilteredByGlobalSalesChannelId();

    /**
     * Gets a list of Magento Websites that use the
     * Sales Channel IDs in the given Sales Channel Groups.
     *
     * @param \RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface[] $salesChannelGroups
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getListFilteredBySalesChannelGroups(array $salesChannelGroups);

    /**
     * Gets a list of Magento Websites that use the globally configured
     * Sales Channel ID and those in the given Sales Channel Groups.
     *
     * @param \RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface[] $salesChannelGroups
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getListFilteredByGlobalSalesChannelIdAndSalesChannelGroups(array $salesChannelGroups);
}
