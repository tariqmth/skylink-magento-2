<?php

namespace spec\RetailExpress\SkyLink\Model\Sales\Shipments;

use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Model\Sales\Shipments\MagentoOrderItemAndSkyLinkFulfillmentGrouper;

class MagentoOrderItemAndSkyLinkFulfillmentGrouperSpec extends ObjectBehavior
{
    private $magentoSimpleProductRepository;

    public function let(MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository)
    {
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;

        $this->beConstructedWith($this->magentoSimpleProductRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoOrderItemAndSkyLinkFulfillmentGrouper::class);
    }
}
