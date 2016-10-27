<?php

namespace spec\RetailExpress\SkyLink\Model\Products;

use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Model\Products\MagentoConfigurableProductService;

class MagentoConfigurableProductServiceSpec extends ObjectBehavior
{
    private $magentoLinkManagement;

    public function let(LinkManagementInterface $magentoLinkManagement)
    {
        $this->magentoLinkManagement = $magentoLinkManagement;

        $this->beConstructedWith($this->magentoLinkManagement);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoConfigurableProductService::class);
    }
}
