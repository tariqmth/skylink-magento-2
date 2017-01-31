<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;

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

    /**
     * Event Manager instance.
     *
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    /**
     * Create a new Sync SkyLink Customer to Magento Customer Handler.
     *
     * @param SkylinkCustomerRepositoryFactory   $skyLinkCustomerRepositoryFactory
     * @param MagentoCustomerRepositoryInterface $magentoCustomerRepository
     * @param MagentoCustomerServiceInterface    $magentoCustomerService
     * @param EventManagerInterface              $eventManager
     * @param SkyLinkLoggerInterface             $logger
     */
    public function __construct(
        SkylinkCustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        MagentoCustomerRepositoryInterface $magentoCustomerRepository,
        MagentoCustomerServiceInterface $magentoCustomerService,
        EventManagerInterface $eventManager,
        SkyLinkLoggerInterface $logger
    ) {
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerService = $magentoCustomerService;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
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

        $this->logger->info('Syncing SkyLink Customer to Magento Customer.', ['SkyLink Customer ID' => $skyLinkCustomerId]);

        /** @var \RetailExpress\SkyLink\Sdk\Customers\CustomerRepository $skyLinkCustomerRepository */
        $skyLinkCustomerRepository = $this->skyLinkCustomerRepositoryFactory->create();

        // Find corresponding SkyLink and Magento Customers
        $skyLinkCustomer = $skyLinkCustomerRepository->find($skyLinkCustomerId);
        $magentoCustomer = $this->magentoCustomerRepository->findBySkyLinkCustomerId($skyLinkCustomerId);

        if (null !== $magentoCustomer) {
            $this->logger->debug('Found Magento Customer exists for SkyLink Customer, updating.', [
                'SkyLink Customer ID' => $skyLinkCustomerId,
                'Magento Customer ID' => $magentoCustomer->getId(),
            ]);
            $this->magentoCustomerService->updateMagentoCustomer($magentoCustomer, $skyLinkCustomer);
        } else {
            $this->logger->debug('No Magento Customer exists for SkyLink Customer, registering.', [
                'SkyLink Customer ID' => $skyLinkCustomerId,
            ]);
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
