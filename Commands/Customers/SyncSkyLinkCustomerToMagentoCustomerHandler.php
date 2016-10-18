<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;

class SyncSkyLinkCustomerToMagentoCustomerHandler
{
    /**
     * SkyLink Customer Repository factory.
     *
     * @var SkyLinkCustomerRepository
     */
    private $skyLinkCustomerRepositoryFactory;

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

    private $eventManager;

    /**
     * Create a new Sync SkyLink Customer to Magento Customer Handler.
     *
     * @param SkylinkCustomerRepositoryFactory   $skyLinkCustomerRepositoryFactory
     * @param MagentoCustomerRepositoryInterface $magentoCustomerRepository
     * @param MagentoCustomerServiceInterface    $magentoCustomerService
     */
    public function __construct(
        SkylinkCustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        MagentoCustomerRepositoryInterface $magentoCustomerRepository,
        MagentoCustomerServiceInterface $magentoCustomerService,
        EventManagerInterface $eventManager
    ) {
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerService = $magentoCustomerService;
        $this->eventManager = $eventManager;
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

        /** @var \RetailExpress\SkyLink\Customers\CustomerRepository $skyLinkCustomerRepository */
        $skyLinkCustomerRepository = $this->skyLinkCustomerRepositoryFactory->create();

        // Find corresponding SkyLink and Magento Customers
        $skyLinkCustomer = $skyLinkCustomerRepository->find($skyLinkCustomerId);
        $magentoCustomer = $this->magentoCustomerRepository->findBySkyLinkCustomerId($skyLinkCustomerId);

        if (null !== $magentoCustomer) {
            $this->magentoCustomerService->updateMagentoCustomer($magentoCustomer, $skyLinkCustomer);
        } else {
            $magentoCustomer = $this->magentoCustomerService->registerMagentoCustomer($skyLinkCustomer);
        }

        $this->eventManager->dispatch(
            'retail_express_skylink_skylink_customer_was_synced_to_magento_customer',
            [
                'command' => $command,
                'skylink_customer' => $skyLinkCustomer,
                'magento_customer' => $magentoCustomer,
            ]
        );
    }
}
