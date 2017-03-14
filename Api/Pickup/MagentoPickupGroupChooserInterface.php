<?php

namespace RetailExpress\SkyLink\Api\Pickup;

interface MagentoPickupGroupChooserInterface
{
    /**
     * Chooses the appropriate Pickup Group for the given Magento Products.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $magentoProducts
     *
     * @return \RetailExpress\SkyLink\Model\Pickup\PickupGroup
     */
    public function choosePickupGroup(array $magentoProducts);
}
