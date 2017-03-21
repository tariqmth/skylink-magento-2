<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;

class SkyLinkCustomerService implements SkyLinkCustomerServiceInterface
{
    private $skyLinkCustomerRepositoryFactory;

    private $magentoCustomerRepository;

    public function __construct(
        SkylinkCustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        CustomerRepositoryInterface $magentoCustomerRepository
    ) {
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function pushSkyLinkCustomer(SkyLinkCustomer $skyLinkCustomer, CustomerInterface $magentoCustomer)
    {
        // Flag for whether we're creating or updating
        $existingSkyLinkCustomer = (bool) $skyLinkCustomer->getId();

        /* @var \RetailExpress\SkyLink\Sdk\Customers\CustomerRepository $skyLinkCustomerRepository */
        $skyLinkCustomerRepository = $this->skyLinkCustomerRepositoryFactory->create();

        // Add our customer to the repository (this creates / updates the given customer in Retail Express)
        $skyLinkCustomerRepository->add($skyLinkCustomer);

        // If we're creating, we'll setup the association between the Magento Customer and the SkyLink Customer
        if (false === $existingSkyLinkCustomer) {
            $magentoCustomer->setCustomAttribute('skylink_customer_id', (string) $skyLinkCustomer->getId());
            $this->magentoCustomerRepository->save($magentoCustomer);
        }
    }
}
