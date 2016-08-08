<?php

namespace spec\RetailExpress\SkyLink\Magento2\Model\System\Config\Source;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Magento2\Model\System\Config\Source\SalesChannelId;

class SalesChannelIdSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SalesChannelId::class);
    }

    function it_should_have_50_options()
    {
        $this->toOptionArray()->shouldHaveCount(50);
    }

    function its_first_option_should_be_1_not_0()
    {
        $this->toOptionArray()[0]['value']->shouldBe(1);
    }
}
