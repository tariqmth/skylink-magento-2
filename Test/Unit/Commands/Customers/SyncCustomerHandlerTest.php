<?php

namespace RetailExpress\SkyLink\Magento2\Test\Unit\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use PHPUnit_Framework_TestCase;
use RetailExpress\SkyLink\Magento2\Api\Customers\CustomerService;
use RetailExpress\SkyLink\Magento2\Commands\Customers\SyncCustomerCommand;
use RetailExpress\SkyLink\Magento2\Commands\Customers\SyncCustomerHandler;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Customers\CustomerNotFoundException;
use RetailExpress\SkyLink\Customers\CustomerRepository as SkylinkCustomerRepository;

class SyncCustomerHandlerTest extends PHPUnit_Framework_TestCase
{
    private $skylinkCustomerRepositoryMock;

    private $customerRepositoryMock;

    private $searchCriteriaBuilderMock;

    private $customerServiceMock;

    private $retailExpressCustomerId;

    private $syncCustomerCommand;

    private $syncCustomerHandler;

    public function setUp()
    {
        $this->skylinkCustomerRepositoryMock = $this->getMock(SkylinkCustomerRepository::class);

        $this->customerRepositoryMock = $this->getMock(CustomerRepositoryInterface::class);

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerServiceMock = $this->getMock(CustomerService::class);

        $this->retailExpressCustomerId = 124001;

        $this->syncCustomerCommand = new SyncCustomerCommand();
        $this->syncCustomerCommand->retailExpressCustomerId = $this->retailExpressCustomerId;

        $this->syncCustomerHandler = new SyncCustomerHandler(
            $this->skylinkCustomerRepositoryMock,
            $this->customerRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->customerServiceMock,
            $this->syncCustomerHandler
        );
    }

    /**
     * @expectedException RetailExpress\SkyLink\Customers\CustomerNotFoundException
     */
    public function testRetrievingNonExistingCustomerPassesOnException()
    {
        $retailExpressCustomerId = new SkyLinkCustomerId($this->retailExpressCustomerId);

        $this->skylinkCustomerRepositoryMock->expects($this->once())
            ->method('find')
            ->with($retailExpressCustomerId)
            ->will($this->throwException(CustomerNotFoundException::withCustomerId($retailExpressCustomerId)));

        $this->syncCustomerHandler->handle($this->syncCustomerCommand);
    }

    /**
     * @expectedException RetailExpress\SkyLink\Magento2\Commands\Customers\TooManyCustomerMatchesException
     */
    public function testFindingExistingCustomerFailsIfThereIsMoreThanOneMatch()
    {
        $this->prepareMocksForSearchingForCustomersWithPredeterminedResultsCount(2);

        $this->syncCustomerHandler->handle($this->syncCustomerCommand);
    }

    public function testThatAnExistingCustomerIsUpdatedWhenAMatchIsFound()
    {
        list(
            $retailExpressCustomerMock,
            $searchResultsMock
        ) = $this->prepareMocksForSearchingForCustomersWithPredeterminedResultsCount(1);

        $customerMock = $this->getMock(CustomerInterface::class);

        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$customerMock]);

        $this->customerServiceMock->expects($this->once())
            ->method('updateCustomer')
            ->with($customerMock, $retailExpressCustomerMock);

        $this->syncCustomerHandler->handle($this->syncCustomerCommand);
    }

    public function testThatANewCustomerIsRegisteredWhenThereAreNoMatchesForExistingCustomers()
    {
        list($retailExpressCustomerMock) = $this->prepareMocksForSearchingForCustomersWithPredeterminedResultsCount(0);

        $this->customerServiceMock->expects($this->once())
            ->method('registerCustomer')
            ->with($retailExpressCustomerMock);

        $this->syncCustomerHandler->handle($this->syncCustomerCommand);
    }

    private function prepareMocksForSearchingForCustomersWithPredeterminedResultsCount($resultCount)
    {
        // Create a Customer ID object
        $retailExpressCustomerId = new SkyLinkCustomerId($this->retailExpressCustomerId);

        // And a Retail Express Customer mock
        $retailExpressCustomerMock = $this->getMockBuilder(SkyLinkCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Calling getId() on the Retail Express Customer returns the correct ID
        $retailExpressCustomerMock->expects($this->once())
            ->method('getId')
            ->willReturn($retailExpressCustomerId);

        // Return the Retail Express Customer from it's repository
        $this->skylinkCustomerRepositoryMock->expects($this->once())
            ->method('find')
            ->with($retailExpressCustomerId)
            ->willReturn($retailExpressCustomerMock);

        // The Search Criteria Builder should receive a filter of the Retail Express customer ID
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with('retail_express_customer_id', (string) $retailExpressCustomerId);

        // Mock the Search Criteria that the Search Criteria Builder returns
        $searchCriteriaMock = $this->getMock(SearchCriteriaInterface::class);

        // Mock creating the Search Criteria
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        // Search Results Mock
        $searchResultsMock = $this->getMock(CustomerSearchResultsInterface::class);

        // When searching against the Customer Repository, return the Search Results Mock
        $this->customerRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);

        // Make sure the Search Results Mock returns a predefined count of results
        $searchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($resultCount);

        // Give back the SkyLinkCustomer and the Search Results for further testing
        return [$retailExpressCustomerMock, $searchResultsMock];
    }
}
