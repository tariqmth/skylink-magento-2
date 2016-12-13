<?php

namespace RetailExpress\SkyLink\Magento\Plugin\Customers;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Registry;
use RetailExpress\CommandBus\Api\CommandBusInterface;

class AddressRepositoryPlugin
{
    use CustomerSyncer;

    /**
     * Create a new Customer Syncer instance.
     *
     * @param CommandBusInterface $commandBus
     * @param Registry            $registry
     */
    public function __construct(
        CommandBusInterface $commandBus,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->commandBus = $commandBus;
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
    }

    public function afterSave(
        AddressRepositoryInterface $subject,
        AddressInterface $magentoAddress
    ) {
        // Sync the customer associated with the address
        $magentoCustomer = $this->customerRepository->getById($magentoAddress->getCustomerId());
        $this->sync($magentoCustomer);

        return $magentoAddress;
    }
}
