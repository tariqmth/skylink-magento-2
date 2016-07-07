<?php

namespace RetailExpress\SkyLink\Test\Unit\Model\Customers;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use PHPUnit_Framework_TestCase;
use RetailExpress\SkyLink\Api\Customers\CustomerMapper;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Customers\ShippingContact as SkyLinkShippingContact;
use RetailExpress\SkyLink\Model\Customers\CustomerAddressExtractor;
use RetailExpress\SkyLink\Model\Customers\CustomerService;

class CustomerServiceTest extends PHPUnit_Framework_TestCase
{
    private $accountManagementMock;

    private $customerRepositoryMock;

    private $customerFactoryMock;

    private $customerAddressExtractorMock;

    private $customerMapperMock;

    private $customerMock;

    private $retailExpressCustomerMock;

    private $retailExpressBillingContactMock;

    private $retailExpressShippingContactMock;

    private $customerService;

    public function setUp()
    {
        $this->accountManagementMock = $this->getMock(AccountManagementInterface::class);

        $this->customerRepositoryMock = $this->getMock(CustomerRepositoryInterface::class);

        $this->customerFactoryMock = $this->getMockBuilder(CustomerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerAddressExtractorMock = $this->getMockBuilder(CustomerAddressExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMapperMock = $this->getMock(CustomerMapper::class);

        $this->customerMock = $this->getMock(CustomerInterface::class);

        $this->retailExpressCustomerMock = $this->getMockBuilder(SkyLinkCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Getting Billing Contact from the Customer mock
        $this->retailExpressBillingContactMock = $this->getMockBuilder(SkyLinkBillingContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->retailExpressCustomerMock->expects($this->any())
            ->method('getBillingContact')
            ->willReturn($this->retailExpressBillingContactMock);

        // Getting Shipping Contact from the customer mock
        $this->retailExpressShippingContactMock = $this->getMockBuilder(SkyLinkShippingContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->retailExpressCustomerMock->expects($this->any())
            ->method('getShippingContact')
            ->willReturn($this->retailExpressShippingContactMock);

        $this->customerService = new CustomerService(
            $this->accountManagementMock,
            $this->customerRepositoryMock,
            $this->customerFactoryMock,
            $this->customerAddressExtractorMock,
            $this->customerMapperMock
        );
    }

    public function testRegisterCustomer()
    {
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerMock);

        $billingAddressMock = $this->getMock(AddressInterface::class);
        $shippingAddressMock = $this->getMock(AddressInterface::class);

        // Mock out extracting addresses
        $this->customerAddressExtractorMock->expects($this->once())
            ->method('extract')
            ->with($this->customerMock)
            ->willReturn([$billingAddressMock, $shippingAddressMock]);

        // New customers have no addresses
        $this->customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        // New customers will get their addresses assigned
        $this->customerMock->expects($this->once())
            ->method('setAddresses')
            ->with([$billingAddressMock, $shippingAddressMock]);

        $this->customerService->registerCustomer($this->retailExpressCustomerMock);
    }

    public function testUpdateCustomer()
    {
        $billingAddressMock = $this->getMock(AddressInterface::class);
        $shippingAddressMock = $this->getMock(AddressInterface::class);

        // Mock out extracting addresses
        $this->customerAddressExtractorMock->expects($this->once())
            ->method('extract')
            ->with($this->customerMock)
            ->willReturn([$billingAddressMock, $shippingAddressMock]);

        // Existingcustomers have the same addresses
        $this->customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$billingAddressMock, $shippingAddressMock]);

        // Existing customers will get their addresses assigned
        $this->customerMock->expects($this->once())
            ->method('setAddresses')
            ->with([$billingAddressMock, $shippingAddressMock]);

        $this->customerService->updateCustomer($this->customerMock, $this->retailExpressCustomerMock);
    }
}
