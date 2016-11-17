<?php

namespace RetailExpress\SkyLink\Plugin\Customers;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerCommand;

class AccountManagementPlugin
{
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function afterCreateAccountWithPasswordHash(
        AccountManagementInterface $subject,
        CustomerInterface $customer
    ) {
        $command = new SyncMagentoCustomerToSkyLinkCustomerCommand();
        $command->magentoCustomerId = $customer->getId();

        $this->commandBus->handle($command);

        return $customer;
    }
}

