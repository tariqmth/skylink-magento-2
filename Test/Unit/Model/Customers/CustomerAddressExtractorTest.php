<?php

namespace RetailExpress\SkyLink\Magento2\Test\Unit\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressFactory;
use PHPUnit_Framework_TestCase;
use RetailExpress\SkyLink\Magento2\Model\Customers\CustomerAddressExtractor;

class CustomerAddressExtractorTest extends PHPUnit_Framework_TestCase
{
    private $addressFactoryMock;

    private $customerMock;

    private $customerAddressExtractor;

    public function setUp()
    {
        $this->addressFactoryMock = $this->getMockBuilder(AddressFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMock = $this->getMock(CustomerInterface::class);

        $this->customerAddressExtractor = new CustomerAddressExtractor(
            $this->addressFactoryMock
        );
    }

    public function testExtractingWhenThereAreDefaults()
    {
        // Mock out a billing address
        $billingAddress = $this->getMock(AddressInterface::class);
        $billingAddress->expects($this->once())
            ->method('isDefaultBilling')
            ->willReturn(true);
        $billingAddress->expects($this->once())
            ->method('isDefaultShipping')
            ->willReturn(false);

        // Mock out a shipping address
        $shippingAddress = $this->getMock(AddressInterface::class);
        $shippingAddress->expects($this->once())
            ->method('isDefaultShipping')
            ->willReturn(true);
        $shippingAddress->expects($this->once())
            ->method('isDefaultBilling')
            ->willReturn(false);

        $this->customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$billingAddress, $shippingAddress]);

        $this->assertEquals(
            [$billingAddress, $shippingAddress],
            $this->customerAddressExtractor->extract($this->customerMock)
        );
    }

    public function testExtractingWhenThereAreNoAddresses()
    {
        // Mock out a billing address
        $billingAddress = $this->getMock(AddressInterface::class);
        $billingAddress->expects($this->once())
            ->method('setIsDefaultBilling')
            ->willReturnSelf();

        $this->addressFactoryMock->expects($this->at(0))
            ->method('create')
            ->willReturn($billingAddress);

        // Mock out a shipping address
        $shippingAddress = $this->getMock(AddressInterface::class);
        $shippingAddress->expects($this->once())
            ->method('setIsDefaultShipping')
            ->willReturnSelf();

        $this->addressFactoryMock->expects($this->at(1))
            ->method('create')
            ->willReturn($shippingAddress);

        $this->customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        $this->assertEquals(
            [$billingAddress, $shippingAddress],
            $this->customerAddressExtractor->extract($this->customerMock)
        );
    }
}
