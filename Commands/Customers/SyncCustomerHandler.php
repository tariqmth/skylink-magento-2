<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Customers\CustomerRepository as SkylinkCustomerRepository;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;

class SyncCustomerHandler
{
    private $skylinkCustomerRepository;

    private $customerRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        SkylinkCustomerRepository $skylinkCustomerRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->skylinkCustomerRepository = $skylinkCustomerRepository;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function handle(SyncCustomerCommand $command)
    {
        $retailExpressCustomer = $this->retrieveRetailExpressCustomer($command->retailExpressCustomerId);

        $customer = $this->findCorrespondingCustomer($retailExpressCustomer);

        if ($customer) {
            // Existing customer, update
        } else {
            // New customer, register
        }
    }

    private function retrieveRetailExpressCustomer($retailExpressCustomerId)
    {
        $retailExpressCustomerId = new SkyLinkCustomerId($retailExpressCustomerId);

        return $this->skylinkCustomerRepository->find($retailExpressCustomerId);
    }

    private function findCorrespondingCustomer(SkyLinkCustomer $retailExpressCustomer)
    {
        $this->searchCriteriaBuilder->addFilter('retail_express_customer_id', (string) $retailExpressCustomer->getId());

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $results = $this->customerRepository->getList($searchCriteria);

        if ($result->getTotalCount() > 1) {
            // Throw exception because there appears to be more than one
            // customer with the same Retail Express Customer ID
        }

        if ($result->getTotalCount() === 1) {
            return $result->getItems()[0];
        }
    }
}
