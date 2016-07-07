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
    /**
     * Retail Express Customer Repository.
     *
     * @var SkyLinkCustomerRepository
     */
    private $skyLinkCustomerRepository;

    /**
     * Customer Repository.
     *
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Search Criteria Builder, used for building filters against the Customer Repository.
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Customer Service, used for updating/registering Customers.
     *
     * @var CustomerService
     */
    private $customerService;

    /**
     * Create a new Sync Customer Handler.
     *
     * @param SkylinkCustomerRepository   $skyLinkCustomerRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param CustomerService             $customerService
     */
    public function __construct(
        SkylinkCustomerRepository $skyLinkCustomerRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerService $customerService
    ) {
        $this->skyLinkCustomerRepository = $skyLinkCustomerRepository;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerService = $customerService;
    }

    /**
     * Synchronises a customer by firstly grabbing the customer from Retail Express and then attempts
     * to match it to an existing Customer in Magento. Depending on whether it finds a match or
     * not, it'll update an existing Customer in Magento or register a whole new one.
     *
     * @param SyncCustomerCommand $command
     */
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

    /**
     * Retrieves a Retail Express Customer from the given numerical representation of it's ID.
     *
     * @param int $retailExpressCustomerId
     *
     * @return SkyLinkCustomer
     */
    private function retrieveRetailExpressCustomer($retailExpressCustomerId)
    {
        $retailExpressCustomerId = new SkyLinkCustomerId($retailExpressCustomerId);

        return $this->skyLinkCustomerRepository->find($retailExpressCustomerId);
    }

    /**
     * Finds an existing Customer within Magento that matches the given Retail Express Customer.
     *
     * @param SkyLinkCustomer $retailExpressCustomer
     *
     * @return CustomerInterface
     *
     * @throws TooManyCustomerMatchesException
     */
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

    /**
     * Instructs the Customer Service to update a given Customer with the information from a SkyLinkCustomer.
     *
     * Used mainly to force type-hints in tests.
     *
     * @param Customer        $customer
     * @param SkyLinkCustomer $retailExpressCustomer
     */
    private function updateCustomer(CustomerInterface $customer, SkyLinkCustomer $retailExpressCustomer)
    {
        $this->customerService->updateCustomer($customer, $retailExpressCustomer);
    }

    /**
     * Instructs the Customer Service to regsiter a new Customer with the information from a SkyLinkCustomer.
     *
     * Used mainly to force type-hints in tests.
     *
     * @param SkyLinkCustomer $retailExpressCustomer
     */
    private function registerCustomer(SkyLinkCustomer $retailExpressCustomer)
    {
        $this->customerService->registerCustomer($retailExpressCustomer);
    }
}
