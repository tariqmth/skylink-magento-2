<?php

namespace RetailExpress\SkyLink\Magento2\Commands\Customers;

use RetailExpress\SkyLink\Customers\CustomerRepository as SkylinkCustomerRepository;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Magento2\Api\Customers\MagentoCustomerRepositoryInterface;
use RetailExpress\SkyLink\Magento2\Api\Customers\MagentoCustomerServiceInterface;

class SyncSkyLinkCustomerToMagentoCustomerHandler
{
    /**
     * SkyLink Customer Repository.
     *
     * @var SkyLinkCustomerRepository
     */
    private $skyLinkCustomerRepository;

    /**
     * Magento Customer Repository.
     *
     * @var MagentoCustomerRepositoryInterface
     */
    private $magentoCustomerRepository;

    /**
     * Customer Service, used for updating/registering Customers.
     *
     * @var MagentoCustomerServiceInterface
     */
    private $magentoCustomerService;

    /**
     * Create a new Sync SkyLink Customer to Magento Customer Handler.
     *
     * @param SkylinkCustomerRepository          $skyLinkCustomerRepository
     * @param MagentoCustomerRepositoryInterface $magentoCustomerRepository
     * @param MagentoCustomerServiceInterface    $magentoCustomerService
     */
    public function __construct(
        SkylinkCustomerRepository $skyLinkCustomerRepository,
        MagentoCustomerRepositoryInterface $magentoCustomerRepository,
        MagentoCustomerServiceInterface $magentoCustomerService
    ) {
        $this->skyLinkCustomerRepository = $skyLinkCustomerRepository;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerService = $magentoCustomerService;
    }

    /**
     * Synchronises a customer by firstly grabbing the customer from SkyLink and then attempts
     * to match it to an existing Customer in Magento. Depending on whether it finds a match or
     * not, it'll update an existing Customer in Magento or register a whole new one.
     *
     * @param SyncSkyLinkCustomerToMagentoCustomerCommand $command
     */
    public function handle(SyncSkyLinkCustomerToMagentoCustomerCommand $command)
    {
        $skyLinkCustomerId = new SkyLinkCustomerId($command->skyLinkCustomerId);

        // Find corresponding SkyLink and Magento Customers
        $skyLinkCustomer = $this->skyLinkCustomerRepository->find($skyLinkCustomerId);
        $magentoCustomer = $this->magentoCustomerRepository->findBySkyLinkCustomerId($skyLinkCustomerId);

        if (null !== $magentoCustomer) {
            $this->magentoCustomerService->updateMagentoCustomer($magentoCustomer, $skyLinkCustomer);
        } else {
            $this->magentoCustomerService->registerMagentoCustomer($skyLinkCustomer);
        }
    }
}
