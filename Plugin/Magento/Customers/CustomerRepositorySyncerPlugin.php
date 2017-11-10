<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Registry;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerHandler;
use RetailExpress\SkyLink\Exceptions\Customers\CustomerRegistryLockException;

class CustomerRepositorySyncerPlugin
{
    use CustomerSyncer;

    private $commandBus;
    private $registry;
    private $customerSyncHandler;

    /**
     * Create a new Customer Syncer instance.
     *
     * @param CommandBusInterface $commandBus
     * @param Registry            $registry
     */
    public function __construct(
        CommandBusInterface $commandBus,
        Registry $registry,
        SyncMagentoCustomerToSkyLinkCustomerHandler $customerSyncHandler
    ) {
        $this->commandBus = $commandBus;
        $this->registry = $registry;
        $this->customerSyncHandler = $customerSyncHandler;
    }

    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $magentoCustomer
    ) {
        // Force handling the sync command now rather than queue it, because orders happen in real time
        if ($this->registry->registry(MagentoCustomerServiceInterface::REGISTRY_LOCK_KEY)) {
            throw CustomerRegistryLockException::withMagentoCustomerId($magentoCustomer->getId());
        }
        $command = new SyncMagentoCustomerToSkyLinkCustomerCommand();
        $command->magentoCustomerId = $magentoCustomer->getId();
        $this->customerSyncHandler->handle($command);

        return $magentoCustomer;
    }
}
