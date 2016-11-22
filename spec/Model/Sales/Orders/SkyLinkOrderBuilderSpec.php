<?php

namespace spec\RetailExpress\SkyLink\Model\Sales\Orders;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Model\Sales\Orders\SkyLinkOrderBuilder;

class SkyLinkOrderBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkOrderBuilder::class);
    }
}
