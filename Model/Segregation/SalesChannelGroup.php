<?php

namespace RetailExpress\SkyLink\Model\Segregation;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;

class SalesChannelGroup implements SalesChannelGroupInterface
{
    private $salesChannelId;

    private $magentoStores = [];

    private $magentoWebsites = [];

    /**
     * {@inheritdoc}
     */
    public function getSalesChannelId()
    {
        return $this->salesChannelId;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalesChannelId(SalesChannelId $salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoStores()
    {
        return $this->magentoStores;
    }

    /**
     * {@inheritdoc}
     */
    public function setMagentoStores(array $magentoStores)
    {
        $this->magentoStores = array_map(function (StoreInterface $magentoStore) {
            return $magentoStore;
        }, $magentoStores);
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoWebsites()
    {
        return $this->magentoWebsites;
    }

    /**
     * {@inheritdoc}
     */
    public function setMagentoWebsites(array $magentoWebsites)
    {
        $this->magentoWebsites = array_map(function (WebsiteInterface $magentoWebsite) {
            return $magentoWebsite;
        }, $magentoWebsites);
    }
}
