<?php

namespace RetailExpress\SkyLink\Model\Segregation;

use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Segregation\MagentoWebsiteRepositoryInterface;

class MagentoWebsiteRepository implements MagentoWebsiteRepositoryInterface
{
    private $baseMagentoWebsiteRepository;

    private $config;

    public function __construct(
        WebsiteRepositoryInterface $baseMagentoWebsiteRepository,
        ConfigInterface $config
    ) {
        $this->baseMagentoWebsiteRepository = $baseMagentoWebsiteRepository;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getListFilteredByGlobalSalesChannelId()
    {
        return array_values(array_filter(
            $this->baseMagentoWebsiteRepository->getList(),
            function (WebsiteInterface $website) {
                if (Store::ADMIN_CODE === $website->getCode()) {
                    return;
                }

                // Filter websites to those who use the globally configured Sales Channel ID
                $websiteSalesChannelId = $this->config->getSalesChannelIdForWebsite($website->getCode());
                return $websiteSalesChannelId->sameValueAs($this->config->getSalesChannelId());
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getListFilteredBySalesChannelGroups(array $salesChannelGroups)
    {
        $websites = [];

        array_walk(
            $salesChannelGroups,
            function (SalesChannelGroupInterface $salesChannelGroup) use (&$websites) {
                array_map(function (WebsiteInterface $website) use (&$websites) {
                    $this->addUniquelyToWebsites($website, $websites);
                }, $salesChannelGroup->getMagentoWebsites());
            }
        );

        return $websites;
    }

    /**
     * {@inheritdoc}
     */
    public function getListFilteredByGlobalSalesChannelIdAndSalesChannelGroups(array $salesChannelGroups)
    {
        $websites = $this->getListFilteredBySalesChannelGroups($salesChannelGroups);

        // Add on unique websites that are globally configured
        array_map(function (WebsiteInterface $website) use (&$websites) {
            $this->addUniquelyToWebsites($website, $websites);
        }, $this->getListFilteredByGlobalSalesChannelId());

        // Because we have unique instances, we don't need to test for equal comparison
        usort($websites, function (WebsiteInterface $website1, WebsiteInterface $website2) {
            return $website1->getId() > $website2->getId() ? 1 : -1;
        });

        return array_values($websites);
    }

    private function addUniquelyToWebsites(WebsiteInterface $newWebsite, &$websites)
    {
        if (array_key_exists($newWebsite->getId(), $websites)) {
            return;
        }

        $websites[$newWebsite->getId()] = $newWebsite;
    }
}
