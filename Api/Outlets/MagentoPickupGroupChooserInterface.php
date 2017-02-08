<?php

namespace RetailExpress\SkyLink\Api\Outlets;

interface MagentoPickupGroupChooserInterface
{
    /**
     * Chooses the appropriate Pickup Group for the given Magento Products.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $magentoProducts
     *
     * @return \RetailExpress\SkyLink\Model\Outlets\PickupGroup
     */
    public function choosePickupGroup(array $magentoProducts);
}
