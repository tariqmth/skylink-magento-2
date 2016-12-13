<?php

namespace RetailExpress\SkyLink\Magento\Plugin\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Registry;
use RetailExpress\CommandBus\Api\CommandBusInterface;

class CustomerRepositoryPlugin
{
    use CustomerSyncer;

    /**
     * Create a new Customer Syncer instance.
     *
     * @param CommandBusInterface $commandBus
     * @param Registry            $registry
     */
    public function __construct(CommandBusInterface $commandBus, Registry $registry)
    {
        $this->commandBus = $commandBus;
        $this->registry = $registry;
    }

    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $magentoCustomer
    ) {
        $this->sync($magentoCustomer);

        return $magentoCustomer;
    }
}
