<?php

namespace RetailExpress\SkyLink\Model\Pickup;

use Magento\Store\Model\StoreManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Pickup\ConfigInterface as PickupConfigInterface;
use RetailExpress\SkyLink\Api\Pickup\PickupOutletRepositoryInterface;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelIdRepositoryInterface;
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

    private $salesChannelIdRepository;

    private $pickupConfig;

    private $skyLinkOutletRepositoryFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        SalesChannelIdRepositoryInterface $salesChannelIdRepository,
        PickupConfigInterface $pickupConfig,
        SkyLinkOutletRepositoryFactory $skyLinkOutletRepositoryFactory
    ) {
        $this->storeManager = $storeManager;
        $this->salesChannelIdRepository = $salesChannelIdRepository;
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
        $salesChannelId = $this->salesChannelIdRepository->getSalesChannelIdForCurrentWebsite();

        return array_map(function (SkyLinkOutletId $skyLinkOutletId) use ($skyLinkOutletRepository, $salesChannelId) {
            return $skyLinkOutletRepository->find($skyLinkOutletId, $salesChannelId);
        }, $configuredOutletIds);
    }
}
