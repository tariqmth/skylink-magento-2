<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Registry;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerMapperInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;

class MagentoCustomerService implements MagentoCustomerServiceInterface
{
    private $magentoAccountManagement;

    private $magentoCustomerRepository;

    private $magentoCustomerFactory;

    private $magentoAddressFactory;

    private $magentoCustomerMapper;

    private $registry;

    public function __construct(
        AccountManagementInterface $magentoAccountManagement,
        CustomerRepositoryInterface $magentoCustomerRepository,
        CustomerInterfaceFactory $magentoCustomerFactory,
        AddressInterfaceFactory $magentoAddressFactory,
        MagentoCustomerMapperInterface $magentoCustomerMapper,
        Registry $registry
    ) {
        $this->magentoAccountManagement = $magentoAccountManagement;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerFactory = $magentoCustomerFactory;
        $this->magentoAddressFactory = $magentoAddressFactory;
        $this->magentoCustomerMapper = $magentoCustomerMapper;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function registerMagentoCustomer(SkyLinkCustomer $skyLinkCustomer)
    {
        $magentoCustomer = $this->magentoCustomerFactory->create();

        // Associate with the given SkyLink Customer
        $magentoCustomer->setCustomAttribute(
            'skylink_customer_id',
            $skyLinkCustomer->getId()->toNative()
        );

        $magentoBillingAddress = $this->createDefaultBillingAddress();
        $magentoShippingAddress = $this->createDefaultShippingAddress();
        $magentoCustomer->setAddresses([$magentoBillingAddress, $magentoShippingAddress]);

        $this->magentoCustomerMapper->mapMagentoCustomer($magentoCustomer, $skyLinkCustomer);

        $this->lockSkyLinkToMagento(function () {
            $this->magentoAccountManagement->createAccount($magentoCustomer);
        });

        return $magentoCustomer;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoCustomer(CustomerInterface $magentoCustomer, SkyLinkCustomer $skyLinkCustomer)
    {
        $addressesToAdd = [];

        if (null === $magentoCustomer->getDefaultBilling()) {
            $addressesToAdd[] = $this->createDefaultBillingAddress();
        }

        if (null === $magentoCustomer->getDefaultShipping()) {
            $addressesToAdd[] = $this->createDefaultShippingAddress();
        }

        if (count($addressesToAdd) > 0) {
            $magentoCustomer->setAddresses(array_merge($magentoCustomer->getAddresses(), $addressesToAdd));
        }

        $this->magentoCustomerMapper->mapMagentoCustomer($magentoCustomer, $skyLinkCustomer);

        $this->lockSkyLinkToMagento(function () {
            $this->magentoCustomerRepository->save($magentoCustomer);
        });
    }

    private function createDefaultBillingAddress()
    {
        $magentoBillingAddress = $this->magentoAddressFactory->create();
        $magentoBillingAddress->setIsDefaultBilling(true);

        return $magentoBillingAddress;
    }

    private function createDefaultShippingAddress()
    {
        $magentoShippingAddress = $this->magentoAddressFactory->create();
        $magentoShippingAddress->setIsDefaultShipping(true);

        return $magentoShippingAddress;
    }

    private function lockSkyLinkToMagento(callable $callback)
    {
        $this->registry->register('skylink_to_magento_lock');
        $callable($callback);
        $this->registry->unregister('skylink_to_magento_lock');
    }
}
