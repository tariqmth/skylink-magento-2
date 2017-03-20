<?php

namespace RetailExpress\SkyLink\Model\Segregation;

use Magento\Store\Model\StoreManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelIdRepositoryInterface;

class SalesChannelIdRepository implements SalesChannelIdRepositoryInterface
{
    /**
     * The Magento Store Manager.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $config;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigInterface $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalesChannelIdForCurrentWebsite()
    {
        /* @var \Magento\Store\Api\Data\WebsiteInterface $currentWebsite */
        $currentWebsite = $this->storeManager->getWebsite();

        return $this->config->getSalesChannelIdForWebsite($currentWebsite->getCode());
    }
}
