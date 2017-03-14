<?php

namespace RetailExpress\SkyLink\Model\Pickup;

use Magento\Store\Model\StoreManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Pickup\ConfigInterface as PickupConfigInterface;
use RetailExpress\SkyLink\Api\Pickup\PickupOutletRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;
use RetailExpress\SkyLink\Sdk\Outlets\OutletRepositoryFactory as SkyLinkOutletRepositoryFactory;

class PickupOutletRepository implements PickupOutletRepositoryInterface
{
    /**
     * The Magento Store Manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * The SkyLink Config instance.
     *
     * @var \RetailExpress\SkyLink\Api\ConfigInterface
     */
    private $config;

    private $pickupConfig;

    private $skyLinkOutletRepositoryFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        PickupConfigInterface $pickupConfig,
        SkyLinkOutletRepositoryFactory $skyLinkOutletRepositoryFactory
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->pickupConfig = $pickupConfig;
        $this->skyLinkOutletRepositoryFactory = $skyLinkOutletRepositoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getListForPickupGroup(PickupGroup $pickupGroup)
    {
        /* @var \Magento\Store\Api\Data\WebsiteInterface $currentWebsite */
        $currentWebsite = $this->storeManager->getWebsite();

        /* @var SkyLinkOutletId[] $configuredOutletIds */
        $configuredOutletIds = $this->pickupConfig->getOutletIdsForWebsite($pickupGroup, $currentWebsite->getCode());

        /* @var \RetailExpress\SkyLink\Sdk\Outlets\OutletRepository $skyLinkOutletRepository */
        $skyLinkOutletRepository = $this->skyLinkOutletRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId $salesChannelId */
        $salesChannelId = $this->config->getSalesChannelIdForWebsite($currentWebsite->getCode());

        return array_map(function (SkyLinkOutletId $skyLinkOutletId) use ($skyLinkOutletRepository, $salesChannelId) {
            return $skyLinkOutletRepository->find($skyLinkOutletId, $salesChannelId);
        }, $configuredOutletIds);
    }
}
