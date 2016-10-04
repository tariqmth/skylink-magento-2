<?php

namespace spec\RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Exceptions\Customers\TooManyCustomerMatchesException;
use RetailExpress\SkyLink\Model\Customers\MagentoCustomerRepository;

class MagentoCustomerRepositorySpec extends ObjectBehavior
{
    private $searchCriteriaBuilder;

    private $baseMagentoCustomerRepository;

    private $skyLinkCustomerId;

    private $skyLinkCustomerIdString;

    public function let(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $baseMagentoCustomerRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;

        $this->beConstructedWith($this->searchCriteriaBuilder, $this->baseMagentoCustomerRepository);

        $this->skyLinkCustomerId = new SkyLinkCustomerId(124001);
        $this->skyLinkCustomerIdString = '124001';
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoCustomerRepository::class);
    }

    public function it_returns_a_customer_when_there_is_one_match(
        SearchCriteria $searchCriteria,
        CustomerSearchResultsInterface $customerSearchResults,
        CustomerInterface $magentoCustomer
    ) {
        $this->prepare_mocks($searchCriteria, $customerSearchResults);

        $customerSearchResults->getTotalCount()->willReturn(1);
        $customerSearchResults->getItems()->willReturn([$magentoCustomer]);

        $this->findBySkyLinkCustomerId($this->skyLinkCustomerId)->shouldReturn($magentoCustomer);
    }

    public function it_returns_null_when_there_are_no_matches(
        SearchCriteria $searchCriteria,
        CustomerSearchResultsInterface $customerSearchResults
    ) {
        $this->prepare_mocks($searchCriteria, $customerSearchResults);

        $customerSearchResults->getTotalCount()->willReturn(0);

        $this->findBySkyLinkCustomerId($this->skyLinkCustomerId)->shouldBe(null);
    }

    public function it_throws_an_exception_if_there_are_too_many_matches(
        SearchCriteria $searchCriteria,
        CustomerSearchResultsInterface $customerSearchResults
    ) {
        $this->prepare_mocks($searchCriteria, $customerSearchResults);

        $customerSearchResults->getTotalCount()->willReturn(2);

        $this
            ->shouldThrow(TooManyCustomerMatchesException::class)
            ->duringFindBySkyLinkCustomerId($this->skyLinkCustomerId);
    }

    private function prepare_mocks(
        SearchCriteria $searchCriteria,
        CustomerSearchResultsInterface $customerSearchResults
    ) {
        $this
            ->searchCriteriaBuilder
            ->addFilter('retail_express_customer_id', $this->skyLinkCustomerIdString)
            ->shouldBeCalled();

        $this->searchCriteriaBuilder->create()->shouldBeCalled()->willReturn($searchCriteria);

        $this
            ->baseMagentoCustomerRepository
            ->getList($searchCriteria)
            ->shouldBeCalled()
            ->willReturn($customerSearchResults);
    }
}
