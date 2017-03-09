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
     * Gets a list of all Magento Websites that will be associated with any
     * data segregation, filtered by the given Sales Channel Groups.
     *
     * @param [] $salesChannelGroups
     *
     * @return \RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface[]
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Segregation\SalesChannelIdMisconfiguredException
     */
    public function getListFilteredBySalesChannelGroups(array $salesChannelGroups)
    {
        $salesChannelGroupWebsites = [];

        array_walk($salesChannelGroups, function (SalesChannelGroupInterface $salesChannelGroup) use (&$salesChannelGroupWebsites) {
            array_map(function (WebsiteInterface $website) use (&$salesChannelGroupWebsites) {
                if (array_key_exists($website->getId(), $salesChannelGroupWebsites)) {
                    return;
                }

                $salesChannelGroupWebsites[$website->getId()] = $website;
            }, $salesChannelGroup->getMagentoWebsites());
        });

        // Sales Channel Groups are only for websites that are not configured with the globallly
        // configured Sales Channel ID. We'll find all the remaining websites now.
        $additionalMagentoWebsites = [];

        array_map(function (WebsiteInterface $website) use ($salesChannelGroupWebsites, &$additionalMagentoWebsites) {
            if (Store::ADMIN_CODE === $website->getCode()) {
                return;
            }

            if (array_key_exists($website->getId(), $salesChannelGroupWebsites)) {
                return;
            }

            // If the Sales Channel ID for this website isn't the same as the global, we know we
            // got a filtered down list of Sales Channel Groups given to us and we should
            // respect that.
            $websiteSalesChannelId = $this->config->getSalesChannelIdForWebsite($website->getCode());
            $globalSalesChannelId = $this->config->getSalesChannelId();
            if (!$websiteSalesChannelId->sameValueAs($globalSalesChannelId)) {
                return;
            }

            $additionalMagentoWebsites[$website->getId()] = $website;
        }, $this->baseMagentoWebsiteRepository->getList());

        $magentoWebsites = $salesChannelGroupWebsites + $additionalMagentoWebsites;
        ksort($magentoWebsites);

        return array_values($magentoWebsites);
    }
}
