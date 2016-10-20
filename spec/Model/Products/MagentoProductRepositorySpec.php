<?php

namespace spec\RetailExpress\SkyLink\Model\Products;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoProductRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('RetailExpress\SkyLink\Model\Products\MagentoProductRepository');
    }
}
