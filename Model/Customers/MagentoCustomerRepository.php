<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Customers\TooManyCustomerMatchesException;

class MagentoCustomerRepository implements MagentoCustomerRepositoryInterface
{
    /**
     * Search Criteria Builder, used for building filters against the Customer Repository.
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Base Magento Customer Repository.
     *
     * @var CustomerRepositoryInterface
     */
    private $baseMagentoCustomerRepository;

    /**
     * Create a new Magento Customer Repository instance.
     *
     * @param CustomerRepositoryInterface $baseMagentoCustomerRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $baseMagentoCustomerRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySkyLinkCustomerId(SkyLinkCustomerId $skyLinkCustomerId)
    {
        $this->searchCriteriaBuilder->addFilter('skylink_customer_id', (string) $skyLinkCustomerId);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $existingCustomers = $this->baseMagentoCustomerRepository->getList($searchCriteria);

        $existingCustomerMatches = $existingCustomers->getTotalCount();

        if ($existingCustomerMatches > 1) {
            throw TooManyCustomerMatchesException::withSkyLinkCustomerId($skyLinkCustomerId, $existingCustomerMatches);
        }

        if ($existingCustomerMatches === 1) {
            return $existingCustomers->getItems()[0];
        }
    }
}
