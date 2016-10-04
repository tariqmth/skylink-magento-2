<?php

namespace spec\RetailExpress\SkyLink\Model\System\Config\Source;

use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Model\System\Config\Source\SalesChannelId;

class SalesChannelIdSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SalesChannelId::class);
    }

    public function it_should_have_fifty_options()
    {
        $this->toOptionArray()->shouldHaveCount(50);
    }

    public function its_first_option_should_be_one_not_zero()
    {
        $this->toOptionArray()[0]['value']->shouldBe(1);
    }
}
