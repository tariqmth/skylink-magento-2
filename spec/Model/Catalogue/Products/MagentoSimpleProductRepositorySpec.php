<?php

namespace spec\RetailExpress\SkyLink\Model\Products;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductLinkRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Model\Products\MagentoSimpleProductRepository;

class MagentoProductRepositorySpec extends ObjectBehavior
{
    private $baseMagentoProductRepository;

    private $searchCriteriaBuilder;

    private $magentoProductLinkRepository;

    public function let(
        ProductRepositoryInterface $baseMagentoProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MagentoProductLinkRepositoryInterface $magentoProductLinkRepository
    ) {
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->magentoProductLinkRepository = $magentoProductLinkRepository;
        $this->productConfig = $productConfig;

        $this->beConstructedWith(
            $this->baseMagentoProductRepository,
            $this->searchCriteriaBuilder,
            $this->magentoProductLinkRepository
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoSimpleProductRepository::class);
    }

    public function it_returns_a_simple_product_if_there_is_one_match(
        SearchCriteria $searchCriteria,
        SkyLinkProductId $skyLinkProductId
    ) {

    }
}
