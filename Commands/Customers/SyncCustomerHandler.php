<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Api\CustomerService;
use RetailExpress\SkyLink\Customers\CustomerRepository as SkylinkCustomerRepository;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;

class SyncCustomerHandler
{
    private $skylinkCustomerRepository;

    private $customerRepository;

    private $searchCriteriaBuilder;

    private $customerService;

    public function __construct(
        SkylinkCustomerRepository $skylinkCustomerRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerService $customerService
    ) {
        $this->skylinkCustomerRepository = $skylinkCustomerRepository;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerService = $customerService;
    }

    public function handle(SyncCustomerCommand $command)
    {
        $retailExpressCustomer = $this->retrieveRetailExpressCustomer($command->retailExpressCustomerId);

        $customer = $this->findExistingCustomer($retailExpressCustomer);

        if ($customer) {
            $this->updateCustomer($customer, $retailExpressCustomer);
        } else {
            $this->registerCustomer($retailExpressCustomer);
        }
    }

    private function retrieveRetailExpressCustomer($retailExpressCustomerId)
    {
        $retailExpressCustomerId = new SkyLinkCustomerId($retailExpressCustomerId);

        return $this->skylinkCustomerRepository->find($retailExpressCustomerId);
    }

    private function findExistingCustomer(SkyLinkCustomer $retailExpressCustomer)
    {
        $retailExpressCustomerId = $retailExpressCustomer->getId();

        $this->searchCriteriaBuilder->addFilter('retail_express_customer_id', (string) $retailExpressCustomerId);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $existingCustomers = $this->customerRepository->getList($searchCriteria);

        $existingCustomerMatches = $existingCustomers->getTotalCount();

        if ($existingCustomerMatches > 1) {
            throw TooManyCustomerMatchesException::withRetailExpressCustomerId($retailExpressCustomerId, $existingCustomerMatches);
        }

        if ($existingCustomerMatches === 1) {
            return $existingCustomers->getItems()[0];
        }
    }

    private function updateCustomer(CustomerInterface $customer, SkyLinkCustomer $retailExpressCustomer)
    {
        $this->customerService->updateCustomer($customer, $retailExpressCustomer);
    }

    private function registerCustomer(SkyLinkCustomer $retailExpressCustomer)
    {
        $this->customerService->registerCustomer($retailExpressCustomer);
    }
}
