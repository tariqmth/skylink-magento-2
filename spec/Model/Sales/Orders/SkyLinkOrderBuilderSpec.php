<?php

namespace spec\RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Catalog\Api\ProductRepositoryInterface;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderAddressExtractorInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkCustomerIdServiceInterface;
use RetailExpress\SkyLink\Model\Sales\Orders\SkyLinkOrderBuilder;

class SkyLinkOrderBuilderSpec extends ObjectBehavior
{
    private $skyLinkCustomerIdService;

    private $magentoOrderAddressExtractor;

    private $magentoProductRepository;

    public function let(
        SkyLinkCustomerIdServiceInterface $skyLinkCustomerIdService,
        MagentoOrderAddressExtractorInterface $magentoOrderAddressExtractor,
        ProductRepositoryInterface $magentoProductRepository
    ) {
        $this->skyLinkCustomerIdService = $skyLinkCustomerIdService;
        $this->magentoOrderAddressExtractor = $magentoOrderAddressExtractor;
        $this->magentoProductRepository = $magentoProductRepository;

        $this->beConstructedWith(
            $this->skyLinkCustomerIdService,
            $this->magentoOrderAddressExtractor,
            $this->magentoProductRepository
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkOrderBuilder::class);
    }
}
