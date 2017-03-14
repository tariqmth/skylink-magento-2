<?php

namespace RetailExpress\SkyLink\Model\Pickup;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Pickup\MagentoPickupGroupChooserInterface;

class MagentoPickupGroupChooser implements MagentoPickupGroupChooserInterface
{
    /**
     * {@inheritdoc}
     */
    public function choosePickupGroup(array $magentoProducts)
    {
        // If there's no products actually provided, we'll end early
        if (0 === count($magentoProducts)) {
            return PickupGroup::getDefault();
        }

        // Let's grab an array of all pickup group attributes
        /* @var \Magento\Framework\Api\AttributeInterface[]|null[] $pickupGroupAttributes */
        $pickupGroupAttributes = array_map(function (ProductInterface $magentoProduct) {
            return $magentoProduct->getCustomAttribute('pickup_group');
        }, $magentoProducts);

        // Now, we'll iterate through all of the attributes and convert them to
        // Pickup Group objects, substituting the default Pickup Group for any
        // attributes that have not been configured.
        $pickupGroups = array_map(function ($pickupGroupAttribute) {
            if (null === $pickupGroupAttribute) {
                return PickupGroup::getDefault();
            }

            return PickupGroup::get($pickupGroupAttribute->getValue());
        }, $pickupGroupAttributes);

        // The way we work is, if we contain either a "none" or "one" Pickup Group
        // (in that order), we'll return that. Failing that, all products must
        // use a "both" pickup group in their user selection, meaning we'll
        // actually return the "two" Pickup Group instead.
        return array_first(
            $pickupGroups,

            // Firstly, look for a "none" Pickup Group
            function ($key, PickupGroup $pickupGroup) {
                return $pickupGroup->sameValueAs(PickupGroup::get('none'));
            },

            // Can't find one? Next step in our hierarchy...
            function () use ($pickupGroups) {
                return array_first(
                    $pickupGroups,

                    // Look for a "one" Pickup Group
                    function ($key, PickupGroup $pickupGroup) {
                        return $pickupGroup->sameValueAs(PickupGroup::get('one'));
                    },

                    // Can't find one? Default to Pickup Group 2
                    PickupGroup::get('two')
                );
            }
        );
    }
}
