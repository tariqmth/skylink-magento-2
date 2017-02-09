<?php

namespace spec\RetailExpress\SkyLink\Model\Outlets;

use PhpSpec\ObjectBehavior;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\AttributeInterface;
use RetailExpress\SkyLink\Model\Outlets\MagentoPickupGroupChooser;
use RetailExpress\SkyLink\Model\Outlets\PickupGroup;

class MagentoPickupGroupChooserSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoPickupGroupChooser::class);
    }

    public function it_returns_the_default_pickup_group_when_no_products_are_passed()
    {
        $pickupGroup = $this->choosePickupGroup([]);

        $pickupGroup->shouldBeAnInstanceOf(PickupGroup::class);
        $pickupGroup->sameValueAs(PickupGroup::getDefault())->shouldBe(true);
    }

    public function it_returns_the_default_pickup_group_if_one_product_doesnt_specify_it(
        ProductInterface $magentoProduct1,
        ProductInterface $magentoProduct2,
        AttributeInterface $magentoAttribute1
    ) {
        $magentoProduct1->getCustomAttribute('pickup_group')->willReturn($magentoAttribute1);
        $magentoProduct2->getCustomAttribute('pickup_group')->willReturn(null);

        $magentoAttribute1->getValue()->willReturn('one');

        $pickupGroup = $this->choosePickupGroup([$magentoProduct1, $magentoProduct2]);
        $pickupGroup->sameValueAs(PickupGroup::getDefault())->shouldBe(true);
    }

    public function it_returns_pickup_group_one_if_one_product_specifies_it(
        ProductInterface $magentoProduct1,
        ProductInterface $magentoProduct2,
        AttributeInterface $magentoAttribute1,
        AttributeInterface $magentoAttribute2
    ) {
        $magentoProduct1->getCustomAttribute('pickup_group')->willReturn($magentoAttribute1);
        $magentoProduct2->getCustomAttribute('pickup_group')->willReturn($magentoAttribute2);

        $magentoAttribute1->getValue()->willReturn('one');
        $magentoAttribute2->getValue()->willReturn('both');

        $pickupGroup = $this->choosePickupGroup([$magentoProduct1, $magentoProduct2]);
        $pickupGroup->sameValueAs(PickupGroup::get('one'))->shouldBe(true);
    }

    public function it_returns_pickup_group_two_all_products_specify_both(
        ProductInterface $magentoProduct1,
        ProductInterface $magentoProduct2,
        AttributeInterface $magentoAttribute1,
        AttributeInterface $magentoAttribute2
    ) {
        $magentoProduct1->getCustomAttribute('pickup_group')->willReturn($magentoAttribute1);
        $magentoProduct2->getCustomAttribute('pickup_group')->willReturn($magentoAttribute2);

        $magentoAttribute1->getValue()->willReturn('both');
        $magentoAttribute2->getValue()->willReturn('both');

        $pickupGroup = $this->choosePickupGroup([$magentoProduct1, $magentoProduct2]);
        $pickupGroup->sameValueAs(PickupGroup::get('two'))->shouldBe(true);
    }
}
