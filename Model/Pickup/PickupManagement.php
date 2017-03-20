<?php

namespace RetailExpress\SkyLink\Model\Pickup;

use Magento\Sales\Model\Order as MagentoOrder;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Pickup\PickupManagementInterface;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelIdRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;
use RetailExpress\SkyLink\Sdk\Outlets\OutletRepositoryFactory as SkyLinkOutletRepositoryFactory;

class PickupManagement implements PickupManagementInterface
{
    private $salesChannelIdRepository;

    private $skyLinkOutletRepositoryFactory;

    public function __construct(
        SalesChannelIdRepositoryInterface $salesChannelIdRepository,
        SkyLinkOutletRepositoryFactory $skyLinkOutletRepositoryFactory
    ) {
        $this->salesChannelIdRepository = $salesChannelIdRepository;
        $this->skyLinkOutletRepositoryFactory = $skyLinkOutletRepositoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoShippingCarrierCode()
    {
        return 'skylinkpickup';
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

        if (!preg_match('/skylinkpickup_outlet_(\d+)/', $shippingCarrierCodeAndMethod, $matches)) {
            return null;
        }

        /* @var \RetailExpress\SkyLink\Sdk\Outlets\OutletRepository $skyLinkOutletRepository */
        $skyLinkOutletRepository = $this->skyLinkOutletRepositoryFactory->create();

        return $skyLinkOutletRepository->find(
            new SkyLinkOutletId($matches[1]),
            $this->salesChannelIdRepository->getSalesChannelIdForCurrentWebsite()
        );
    }
}
