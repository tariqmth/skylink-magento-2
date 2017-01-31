<?php

namespace spec\RetailExpress\SkyLink\Exceptions\Sales\Orders;

use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\MagentoOrderItemNotLinkedToSkyLinkProductException;

class MagentoOrderItemNotLinkedToSkyLinkProductExceptionSpec extends ObjectBehavior
{
    private $magentoProductId;

    public function let()
    {
        $this->magentoProductId = 1;
    }

    public function it_is_initializable()
    {
        $this->beConstructedThrough('withMagentoProductId', [$this->magentoProductId]);
        $this->shouldHaveType(MagentoOrderItemNotLinkedToSkyLinkProductException::class);
    }

    public function it_has_a_useful_error_message()
    {
        $this->beConstructedThrough('withMagentoProductId', [$this->magentoProductId]);
        $this->getMessage()->shouldBe('Magento Product #1 is not linked to a SkyLink Product, cannot use in order.');
    }
}
