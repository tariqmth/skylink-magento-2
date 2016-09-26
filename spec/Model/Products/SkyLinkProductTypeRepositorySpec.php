<?php

namespace spec\RetailExpress\SkyLink\Magento2\Model\Products;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Catalogue\Attributes\Attribute;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository;
use RetailExpress\SkyLink\Magento2\Model\Products\SkyLinkProductTypeRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;

class SkyLinkProductTypeRepositorySpec extends ObjectBehavior
{
    private $attributeRepository;

    private $salesChannelId;

    public function let(
        AttributeRepository $attributeRepository,
        SalesChannelId $salesChannelId
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->salesChannelId = $salesChannelId;

        $this->beConstructedWith($this->attributeRepository, $this->salesChannelId);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkProductTypeRepository::class);
    }

    public function it_returns_no_options_if_there_are_none_on_the_attribute()
    {
        $dummyAttribute = Attribute::fromNative(AttributeCode::PRODUCT_TYPE);

        $this
            ->attributeRepository
            ->find(
                AttributeCode::get(AttributeCode::PRODUCT_TYPE),
                $this->salesChannelId
            )
            ->willReturn($dummyAttribute);

        $this->getList()->shouldBeArray();
        $this->getList()->shouldHaveCount(0);
    }

    public function it_returns_the_attributes_options()
    {
        $dummyAttributeOption = AttributeOption::fromNative(
            AttributeCode::PRODUCT_TYPE,
            '1',
            'Winter Clothes'
        );

        $dummyAttribute = Attribute::fromNative(AttributeCode::PRODUCT_TYPE)
            ->withOption($dummyAttributeOption);

        $this
            ->attributeRepository
            ->find(
                AttributeCode::get(AttributeCode::PRODUCT_TYPE),
                $this->salesChannelId
            )
            ->willReturn($dummyAttribute);

        $this->getList()->shouldHaveCount(1);
        $this->getList()->shouldHaveKey(0);
        $this->getList()[0]->sameValueAs($dummyAttributeOption)->shouldBe(true);
    }
}
