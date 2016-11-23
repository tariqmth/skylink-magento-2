<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerBuilderInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerServiceInterface;

class SyncMagentoCustomerToSkyLinkCustomerHandler
{
    private $baseMagentoCustomerRepository;

    private $skyLinkCustomerBuilder;

    private $skyLinkCustomerService;

    public function __construct(
        CustomerRepositoryInterface $baseMagentoCustomerRepository,
        SkyLinkCustomerBuilderInterface $skyLinkCustomerBuilder,
        SkyLinkCustomerServiceInterface $skyLinkCustomerService
    ) {
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
        $this->skyLinkCustomerBuilder = $skyLinkCustomerBuilder;
        $this->skyLinkCustomerService = $skyLinkCustomerService;
    }

    public function handle(SyncMagentoCustomerToSkyLinkCustomerCommand $command)
    {
        /* @var \Magento\Customer\Api\Data\CustomerInterface $magentoCustomer */
        $magentoCustomer = $this->baseMagentoCustomerRepository->getById($command->magentoCustomerId);

        // We'll build an immutable SkyLink Customer object from the given Magento Customer
        $skyLinkCustomer = $this->skyLinkCustomerBuilder->buildFromMagentoCustomer($magentoCustomer);

        // And push the SkyLink Customer up
        $this->skyLinkCustomerService->pushSkyLinkCustomer($skyLinkCustomer, $magentoCustomer);
    }
}
