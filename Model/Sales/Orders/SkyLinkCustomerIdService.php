<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkCustomerIdServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerHandler;
use RetailExpress\SkyLink\Exceptions\Customers\CustomerRegistryLockException;
use Magento\Framework\Registry;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;

class SkyLinkCustomerIdService implements SkyLinkCustomerIdServiceInterface
{
    private $baseMagentoCustomerRepository;

    private $orderConfig;

    private $customerSyncHandler;

    private $registry;

    public function __construct(
        CustomerRepositoryInterface $baseMagentoCustomerRepository,
        ConfigInterface $orderConfig,
        SyncMagentoCustomerToSkyLinkCustomerHandler $customerSyncHandler,
        Registry $registry
    ) {
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
        $this->orderConfig = $orderConfig;
        $this->customerSyncHandler = $customerSyncHandler;
        $this->registry = $registry;
    }

    public function determineSkyLinkCustomerId(OrderInterface $magentoOrder)
    {
        // If the Magento Order is using a guest customer,
        // we'll just grab the mapped guest customer ID.
        if ($magentoOrder->getCustomerIsGuest()) {
            return $this->orderConfig->getGuestCustomerId();
        }

        // Otherwise, we'll find the Magento Customer and grab their SkyLink Customer Id
        $magentoCustomer = $this->baseMagentoCustomerRepository->getById($magentoOrder->getCustomerId());

        /* @var \Magento\Framework\Api\AttributeInterface|null $skyLinkCustomerIdAttribute */
        $skyLinkCustomerIdAttribute = $magentoCustomer->getCustomAttribute('skylink_customer_id');

        if (null === $skyLinkCustomerIdAttribute) {
            if ($this->registry->registry(MagentoCustomerServiceInterface::REGISTRY_LOCK_KEY)) {
                throw CustomerRegistryLockException::withMagentoCustomerId($magentoCustomer->getId());
            }
            $command = new SyncMagentoCustomerToSkyLinkCustomerCommand();
            $command->magentoCustomerId = $magentoCustomer->getId();
            $this->customerSyncHandler->handle($command);
            $magentoCustomer = $this->baseMagentoCustomerRepository->getById($magentoOrder->getCustomerId());
            $skyLinkCustomerIdAttribute = $magentoCustomer->getCustomAttribute('skylink_customer_id');
        }

        return new SkyLinkCustomerId($skyLinkCustomerIdAttribute->getValue());
    }
}
