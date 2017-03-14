<?php

namespace RetailExpress\SkyLink\Model\Pickup;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Pickup\PickupManagementInterface;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;
use RetailExpress\SkyLink\Sdk\Outlets\OutletRepositoryFactory as SkyLinkOutletRepositoryFactory;


class PickupManagement implements PickupManagementInterface
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

    private $skyLinkOutletRepositoryFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        SkyLinkOutletRepositoryFactory $skyLinkOutletRepositoryFactory
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->skyLinkOutletRepositoryFactory = $skyLinkOutletRepositoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoShippingCarrierCode()
    {
        return 'skylink_pickup';
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoShippingMethodCode(SkyLinkOutlet $skyLinkOutlet)
    {
        return sprintf('outlet_%s', $skyLinkOutlet->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoShippingMethodTitle(SkyLinkOutlet $skyLinkOutlet)
    {
        return sprintf('%s - %s', $skyLinkOutlet->getName(), $skyLinkOutlet->getAddress());
    }

    /**
     * {@inheritdoc}
     */
    public function determineSkyLinkOutletToPickupFrom(MagentoOrder $magentoOrder)
    {
        $shippingCarrierCodeAndMethod = $magentoOrder->getData('shipping_method');

        if (!preg_match('/skylink_pickup_outlet_(\d+)/', $shippingCarrierCodeAndMethod, $matches)) {
            return null;
        }

        /* @var \Magento\Store\Api\Data\WebsiteInterface $currentWebsite */
        $currentWebsite = $this->storeManager->getWebsite();

        /* @var \RetailExpress\SkyLink\Sdk\Outlets\OutletRepository $skyLinkOutletRepository */
        $skyLinkOutletRepository = $this->skyLinkOutletRepositoryFactory->create();

        return $skyLinkOutletRepository->find(
            new SkyLinkOutletId($matches[1]),
            $this->getSalesChannelIdForCurrentWebsite()
        );
    }

    /**
     * @return \RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId
     */
    private function getSalesChannelIdForCurrentWebsite()
    {
        /* @var \Magento\Store\Api\Data\WebsiteInterface $currentWebsite */
        $currentWebsite = $this->storeManager->getWebsite();

        return $this->config->getSalesChannelIdForWebsite($currentWebsite->getCode());
    }
}
