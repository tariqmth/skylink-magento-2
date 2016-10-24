<?php

namespace spec\RetailExpress\SkyLink\Model\Products;

use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\Attribute;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepository;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepositoryFactory;
use RetailExpress\SkyLink\Model\Products\SkyLinkProductTypeRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;

class SkyLinkProductTypeRepositorySpec extends ObjectBehavior
{
    private $attributeRepositoryFactory;

    private $attributeRepository;

    private $config;

    private $salesChannelId;

    public function let(
        AttributeRepositoryFactory $attributeRepositoryFactory,
        AttributeRepository $attributeRepository,
        ConfigInterface $config,
        SalesChannelId $salesChannelId
    ) {
        $this->attributeRepositoryFactory = $attributeRepositoryFactory;
        $this->attributeRepository = $attributeRepository;
        $this->config = $config;
        $this->salesChannelId = $salesChannelId;

        $this->beConstructedWith($this->attributeRepositoryFactory, $this->config);

        $this->attributeRepositoryFactory->create()->willReturn($this->attributeRepository);
        $this->config->getSalesChannelId()->willReturn($salesChannelId);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkProductTypeRepository::class);
    }

    public function it_returns_no_options_if_there_are_none_on_the_attribute(SalesChannelId $salesChannelId)
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

    public function it_returns_the_attributes_options(SalesChannelId $salesChannelId)
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
