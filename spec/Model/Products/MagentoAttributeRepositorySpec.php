<?php

namespace spec\RetailExpress\SkyLink\Model\Products;

use Magento\Framework\App\ResourceConnection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Model\Products\MagentoAttributeRepository;

class MagentoAttributeRepositorySpec extends ObjectBehavior
{
    private $resourceConnection;

    public function let(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;

        $this->beConstructedWith($this->resourceConnection);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoAttributeRepository::class);
    }
}
