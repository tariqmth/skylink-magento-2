<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerBuilderInterface;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;

class SyncMagentoCustomerToSkyLinkCustomerHandler
{
    private $baseMagentoCustomerRepository;

    private $skyLinkCustomerRepositoryFactory;

    private $skyLinkCustomerBuilder;

    public function __construct(
        CustomerRepositoryInterface $baseMagentoCustomerRepository,
        SkylinkCustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        SkyLinkCustomerBuilderInterface $skyLinkCustomerBuilder
    ) {
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->skyLinkCustomerBuilder = $skyLinkCustomerBuilder;
    }

    public function handle(SyncMagentoCustomerToSkyLinkCustomerCommand $command)
    {
        /* @var \Magento\Customer\Api\Data\CustomerInterface $magentoCustomer */
        $magentoCustomer = $this->baseMagentoCustomerRepository->getById($command->magentoCustomerId);

        // We'll build an immutable SkyLink Customer object from the given Magento Customer
        $skyLinkCustomer = $this->skyLinkCustomerBuilder->buildFromMagentoCustomer($magentoCustomer);

        /* @var \RetailExpress\SkyLink\Sdk\Customers\CustomerRepository $skyLinkCustomerRepository */
        $skyLinkCustomerRepository = $this->skyLinkCustomerRepositoryFactory->create();

        // Add our customer to the repository (this creates / updates the given customer in Retail Express)
        $skyLinkCustomerRepository->add($skyLinkCustomer);
    }
}
