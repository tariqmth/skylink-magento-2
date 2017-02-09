<?php

namespace RetailExpress\SkyLink\Model\Outlets;

use Magento\Sales\Model\Order as MagentoOrder;
use RetailExpress\SkyLink\Api\Outlets\PickupManagementInterface;
use RetailExpress\SkyLink\Api\Outlets\SkyLinkOutletRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;

class PickupManagement implements PickupManagementInterface
{
    private $skyLinkOutletRepository;

    public function __construct(SkyLinkOutletRepositoryInterface $skyLinkOutletRepository)
    {
        $this->skyLinkOutletRepository = $skyLinkOutletRepository;
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

        return $this->skyLinkOutletRepository->get(new SkyLinkOutletId($matches[1]));
    }
}
