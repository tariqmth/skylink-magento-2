<?php

namespace spec\RetailExpress\SkyLink\Model\Products;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Model\Products\MagentoAttributeRepository;

class MagentoAttributeRepositorySpec extends ObjectBehavior
{
    private $resourceConnection;

    private $magentoProductAttributeRepository;

    public function let(
        ResourceConnection $resourceConnection,
        ProductAttributeRepositoryInterface $magentoProductAttributeRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->magentoProductAttributeRepository = $magentoProductAttributeRepository;

        $this->beConstructedWith($this->resourceConnection, $this->magentoProductAttributeRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoAttributeRepository::class);
    }
}
