<?php

namespace spec\RetailExpress\SkyLink\Model\Sales\Shipments;

use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Model\Sales\Shipments\MagentoOrderItemAndSkyLinkFulfillmentGrouper;

class MagentoOrderItemAndSkyLinkFulfillmentGrouperSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoOrderItemAndSkyLinkFulfillmentGrouper::class);
    }
}
