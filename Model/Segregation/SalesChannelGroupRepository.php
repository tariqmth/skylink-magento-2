<?php

namespace RetailExpress\SkyLink\Model\Segregation;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterfaceFactory;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelGroupRepositoryInterface;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepositoryFactory as SkyLinkProductRepositoryFactory;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterfaceFactory;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface;

class SalesChannelGroupRepository implements SalesChannelGroupRepositoryInterface
{
    const CONFIG_VALUE = 'skylink/general/sales_channel_id';

    private $config;

    private $storeManager;

    private $storeRepository;

    private $websiteRepository;

    private $salesChannelGroupFactory;

    private $skyLinkProductRepositoryFactory;

    private $skyLinkProductInSalesChannelGroupFactory;

    private $skyLinkProductRepository;

    public function __construct(
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        StoreRepositoryInterface $storeRepository,
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelGroupInterfaceFactory $salesChannelGroupFactory,
        SkyLinkProductRepositoryFactory $skyLinkProductRepositoryFactory,
        SkyLinkProductInSalesChannelGroupInterfaceFactory $skyLinkProductInSalesChannelGroupFactory
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelGroupFactory = $salesChannelGroupFactory;
        $this->skyLinkProductRepositoryFactory = $skyLinkProductRepositoryFactory;
        $this->skyLinkProductInSalesChannelGroupFactory = $skyLinkProductInSalesChannelGroupFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @todo refactor this, it's inefficient becuase we know that a Sales Channel ID is common to all stores in a website
     */
    public function getList()
    {
        $groups = $this->getGroupsOfStoresWithWebsite();
        $valuesToGroups = [];

        array_walk($groups, function (array $group) use (&$valuesToGroups) {
            $value = $this->config->getSalesChannelIdForWebsite($group['website']->getCode())->toNative();
            $valuesToGroups[$value][] = $group;
        });

        // Now we'll convert our payload to the requested group
        $salesChannelGroups = [];
        array_walk($valuesToGroups, function (array $groups, $value) use (&$salesChannelGroups) {
            $salesChannelGroup = $this->salesChannelGroupFactory->create();
            $salesChannelGroup->setSalesChannelId(new SalesChannelId($value));

            array_walk($groups, function (array $group) use ($salesChannelGroup) {
                $salesChannelGroup->setMagentoStores(array_merge(
                    $salesChannelGroup->getMagentoStores(),
                    $group['stores']
                ));

                $salesChannelGroup->setMagentoWebsites(array_merge(
                    $salesChannelGroup->getMagentoWebsites(),
                    [$group['website']]
                ));
            });

            $salesChannelGroups[] = $salesChannelGroup;
        });

        return $salesChannelGroups;
    }

    private function getGroupsOfStoresWithWebsite()
    {
        $storesByWebsite = [];
        array_map(function (StoreInterface $store) use (&$storesByWebsite) {
            $storesByWebsite[$store->getWebsiteId()][] = $store;
        }, $this->storeRepository->getList());

        $groups = [];
        array_walk($storesByWebsite, function (array $stores, $websiteId) use (&$groups) {
            $website = $this->websiteRepository->getById($websiteId);

            if (Store::ADMIN_CODE === $website->getCode()) {
                return;
            }

            $groups[] = compact('stores', 'website');
        });

        return $groups;
    }

    public function getSkyLinkProductInSalesChannelGroups(SkyLinkProductId $skyLinkProductId, $useSimple = true)
    {
        if (null === $this->skyLinkProductRepository) {
            $this->skyLinkProductRepository = $this->skyLinkProductRepositoryFactory->create();
        }

        $salesChannelGroups = $this->getList();

        // We'll loop through the Sales Channel Groups and grab the product in the context of each
        $productInSalesChannelGroups = [];
        array_walk(
            $salesChannelGroups,
            function (SalesChannelGroupInterface $salesChannelGroup) use ($skyLinkProductId, &$productInSalesChannelGroups) {
                $skyLinkProduct = $this->skyLinkProductRepository->find(
                    $skyLinkProductId,
                    $salesChannelGroup->getSalesChannelId()
                );

                if (null === $skyLinkProduct) {
                    return;
                }

                //if ($useSimple && )

                $productInSalesChannelGroup = $this->skyLinkProductInSalesChannelGroupFactory->create();
                $productInSalesChannelGroup->setSkyLinkProduct($skyLinkProduct);
                $productInSalesChannelGroup->setSalesChannelGroup($salesChannelGroup);

                $productInSalesChannelGroups[] = $productInSalesChannelGroup;
            }
        );

        return $productInSalesChannelGroups;
    }
}
