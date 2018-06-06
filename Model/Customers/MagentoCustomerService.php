<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Registry;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerMapperInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;
use Throwable;

class MagentoCustomerService implements MagentoCustomerServiceInterface
{
    private $magentoAccountManagement;

    private $magentoCustomerRepository;

    private $magentoCustomerFactory;

    private $magentoCustomerMapper;

    private $registry;

    public function __construct(
        AccountManagementInterface $magentoAccountManagement,
        CustomerRepositoryInterface $magentoCustomerRepository,
        CustomerInterfaceFactory $magentoCustomerFactory,
        MagentoCustomerMapperInterface $magentoCustomerMapper,
        Registry $registry
    ) {
        $this->magentoAccountManagement = $magentoAccountManagement;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerFactory = $magentoCustomerFactory;
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

        $this->magentoCustomerMapper->mapMagentoCustomer($magentoCustomer, $skyLinkCustomer);

        $this->lockSkyLinkToMagento(function () use ($magentoCustomer) {
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

        if (count($addressesToAdd) > 0) {
            $magentoCustomer->setAddresses(array_merge($magentoCustomer->getAddresses(), $addressesToAdd));
        }

        $this->magentoCustomerMapper->mapMagentoCustomer($magentoCustomer, $skyLinkCustomer);

        $this->lockSkyLinkToMagento(function () use ($magentoCustomer) {
            $this->magentoCustomerRepository->save($magentoCustomer);
        });
    }

    private function lockSkyLinkToMagento(callable $callback)
    {
        $this->registry->register(self::REGISTRY_LOCK_KEY, true);
        try {
            $callback();
        } catch (Throwable $e) { // PHP 7+
            $this->unlockSkyLinkToMagento();
            throw $e;
        } catch (Exception $e) {
            $this->unlockSkyLinkToMagento();
            throw $e;
        }

        $this->unlockSkyLinkToMagento();
    }

    private function unlockSkyLinkToMagento()
    {
        $this->registry->unregister(self::REGISTRY_LOCK_KEY);
    }
}
