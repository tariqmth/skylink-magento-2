<?php

namespace RetailExpress\SkyLink\Api\Data\Segregation;

use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;

interface SalesChannelGroupInterface
{
    /**
     * Gets the Sales Channel ID used for the group.
     *
     * @return SalesChannelId
     */
    public function getSalesChannelId();

    /**
     * Sets the Sales Channel ID used for the group.
     *
     * @param SalesChannelId $salesChannelId
     */
    public function setSalesChannelId(SalesChannelId $salesChannelId);

    /**
     * Returns the Magento Websites associated with the group.
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getMagentoWebsites();

    /**
     * Returns the Magento Websites associated with the group.
     *
     * @param \Magento\Store\Api\Data\WebsiteInterface[] $magentoWebsites
     */
    public function setMagentoWebsites(array $magentoWebsites);
}
