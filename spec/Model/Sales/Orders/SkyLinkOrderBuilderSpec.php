<?php

namespace spec\RetailExpress\SkyLink\Model\Sales\Orders;

use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Model\Sales\Orders\SkyLinkOrderBuilder;

class SkyLinkOrderBuilderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkOrderBuilder::class);
    }
}
