<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkCustomerIdServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;

class SkyLinkCustomerIdService implements SkyLinkCustomerIdServiceInterface
{
    private $baseMagentoCustomerRepository;

    private $orderConfig;

    public function __construct(
        CustomerRepositoryInterface $baseMagentoCustomerRepository,
        ConfigInterface $orderConfig
    ) {
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
        $this->orderConfig = $orderConfig;
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
            // @todo throw exeption - mapping should have occured
        }

        return new SkyLinkCustomerId($skyLinkCustomerIdAttribute->getValue());
    }
}
