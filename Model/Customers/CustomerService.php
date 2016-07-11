<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use RetailExpress\SkyLink\Api\Customers\CustomerMapper;
use RetailExpress\SkyLink\Api\Customers\CustomerService as CustomerServiceInterface;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;

class CustomerService implements CustomerServiceInterface
{
    private $accountManagement;

    private $customerRepository;

    private $customerFactory;

    private $customerAddressExtractor;

    private $customerMapper;

    public function __construct(
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        CustomerAddressExtractor $customerAddressExtractor,
        CustomerMapper $customerMapper
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerAddressExtractor = $customerAddressExtractor;
        $this->customerMapper = $customerMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function registerCustomer(SkyLinkCustomer $retailExpressCustomer)
    {
        $customer = $this->customerFactory->create();

        list($billingAddress, $shippingAddress) = $this->customerAddressExtractor->extract($customer);

        $this->map($customer, $billingAddress, $shippingAddress, $retailExpressCustomer);
        $this->mergeAddresses($customer, $billingAddress, $shippingAddress);

        $this->accountManagement->createAccount($customer);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomer(CustomerInterface $customer, SkyLinkCustomer $retailExpressCustomer)
    {
        list($billingAddress, $shippingAddress) = $this->extractAddresses($customer);

        $this->map($customer, $billingAddress, $shippingAddress, $retailExpressCustomer);
        $this->mergeAddresses($customer, $billingAddress, $shippingAddress);

        $this->customerRepository->save($customer);
    }

    /**
     * Extracts the Billing and Shipping Addresses from a customer.
     *
     * @param CustomerInterface $customer
     *
     * @return AddressInterface[]
     */
    private function extractAddresses(CustomerInterface $customer)
    {
        return $this->customerAddressExtractor->extract($customer);
    }

    /**
     * Calls to the Customer Mapper to map the provided Customer, Billing and Shipping Addresses
     * from the given Retail Express Customer.
     *
     * @param CustomerInterface $customer,
     * @param AddressInterface  $billingAddress,
     * @param AddressInterface  $shippingAddress,
     * @param SkyLinkCustomer   $retailExpressCustomer
     *
     * @return AddressInterface
     */
    private function map(
        CustomerInterface $customer,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress,
        SkyLinkCustomer $retailExpressCustomer
    ) {
        $this->customerMapper->mapCustomer($customer, $retailExpressCustomer);

        $this->customerMapper->mapBillingAddress($billingAddress, $retailExpressCustomer->getBillingContact());

        $this->customerMapper->mapShippingAddress($shippingAddress, $retailExpressCustomer->getShippingContact());
    }

    /**
     * Merges the given Billing and Shipping Addresses into the Customer's existing (if any) addresses.
     *
     * @param CustomerInterface $customer,
     * @param AddressInterface  $billingAddress,
     * @param AddressInterface  $shippingAddress
     */
    private function mergeAddresses(
        CustomerInterface $customer,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress
    ) {
        $addresses = $customer->getAddresses();

        foreach ([$billingAddress, $shippingAddress] as $newAddress) {
            if (in_array($newAddress, $addresses, true)) {
                continue;
            }

            $addresses[] = $newAddress;
        }

        $customer->setAddresses($addresses);
    }
}
