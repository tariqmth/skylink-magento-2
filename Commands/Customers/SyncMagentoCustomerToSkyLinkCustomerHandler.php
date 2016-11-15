<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;

class SyncMagentoCustomerToSkyLinkCustomerHandler
{
    private $baseMagentoCustomerRepository;

    private $skyLinkCustomerRepositoryFactory;

    private $skyLinkCustomerService;

    public function __construct(
        CustomerRepositoryInterface $baseMagentoCustomerRepository,
        SkylinkCustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        SkyLinkCustomerServiceInterface $skyLinkCustomerService
    ) {
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory
        $this->skyLinkCustomerService = $skyLinkCustomerService;
    }

    public function handle(SyncMagentoCustomerToSkyLinkCustomerCommand $command)
    {
        /* @var \Magento\Customer\Api\Data\CustomerInterface $magentoCustomer */
        $magentoCustomer = $this->baseMagentoCustomerRepository->getById($command->magentoCustomerId);

        /* @var \RetailExpress\SkyLink\Sdk\Customers\CustomerRepository $skyLinkCustomerRepository */
        $skyLinkCustomerRepository = $this->skyLinkCustomerRepositoryFactory->create();

        /* @var \Magento\Framework\Api\AttributeInterface|null $skyLinkCustomerIdAttribute */
        $skyLinkCustomerIdAttribute = $magentoCustomer->getCustomAttribute('skylink_customer_id');

        // Updating an existing customer
        if (null !== $skyLinkCustomerIdAttribute) {
            $skyLinkCustomerId = new SkyLinkCustomerId($skyLinkCustomerIdAttribute->getValue());

            /** @var \\RetailExpress\SkyLink\Sdk\Customers\Customer $skyLinkCustomer */
            $skyLinkCustomer = $skyLinkCustomerRepository->find($skyLinkCustomerId);

            $this->skyLinkCustomerService->updateSkyLinkCustomer($skyLinkCustomer, $magentoCustomer);

        // Creating a new cusotmer
        } else {
            $this->skyLinkCustomerService->registerSkyLinkCustomer($magentoCustomer);
        }
    }
}
