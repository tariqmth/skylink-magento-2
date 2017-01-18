<?php

namespace RetailExpress\SkyLink\Model\Segregation;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterfaceFactory;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelGroupRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Segregation\SalesChannelIdMisconfiguredException;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;

class SalesChannelGroupRepository implements SalesChannelGroupRepositoryInterface
{
    const CONFIG_VALUE = 'skylink/general/sales_channel_id';
    const ADMIN_WEBSITE_CODE = 'admin';

    private $scopeConfig;

    private $websiteRepository;

    private $salesChannelGroupFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelGroupInterfaceFactory $salesChannelGroupFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelGroupFactory = $salesChannelGroupFactory;
    }

    /**
     * Gets a list of Sales Channel Groups.
     *
     * @return \RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface[]
     */
    public function getList()
    {
        $valuesToWebsites = [];
        array_map(function (WebsiteInterface $website) use (&$valuesToWebsites) {

            $websiteCode = $website->getCode();
            if (self::ADMIN_WEBSITE_CODE === $websiteCode) {
                return;
            }

            $value = $this->scopeConfig->getValue(self::CONFIG_VALUE, 'website', $websiteCode);

            if (!is_numeric($value)) {
                throw SalesChannelIdMisconfiguredException::forWebsiteWithConfigValue($website, $value);
            }

            $valuesToWebsites[$value][] = $website;
        }, $this->websiteRepository->getList());

        // We'll make sure we remove any values to websites that match the global config value
        $valuesToWebsites = array_diff_key($valuesToWebsites, array_flip([$this->getGlobalConfigValue()]));

        // Now we'll convert our payload to the requested group
        $salesChannelGroups = [];
        array_walk($valuesToWebsites, function (array $websites, $value) use (&$salesChannelGroups) {
            $salesChannelGroup = $this->salesChannelGroupFactory->create();
            $salesChannelGroup->setSalesChannelId(new SalesChannelId($value));
            $salesChannelGroup->setMagentoWebsites($websites);
            $salesChannelGroups[] = $salesChannelGroup;
        });

        return $salesChannelGroups;
    }

    private function getGlobalConfigValue()
    {
        return $this->scopeConfig->getValue(self::CONFIG_VALUE);
    }
}
