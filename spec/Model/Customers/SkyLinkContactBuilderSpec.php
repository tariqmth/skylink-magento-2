<?php

namespace spec\RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Model\Customers\SkyLinkContactBuilder;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;

class SkyLinkContactBuilderSpec extends ObjectBehavior
{
    private $valuesToAssert = [];

    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkContactBuilder::class);
    }

    public function it_builds_a_skylink_billing_contact_from_the_magento_customer_address(
        AddressInterface $magentoCustomerAddress,
        RegionInterface $magentoCustomerRegion
    ) {
        $this->setupMagentoCustomerAddressStub($magentoCustomerAddress, $magentoCustomerRegion);
        $this->valuesToAssert['emailAddress'] = 'ben@retailexpress.com.au';

        $skyLinkBillingContact = $this->buildSkyLinkBillingContactFromMagentoCustomerAddress(
            $magentoCustomerAddress,
            $this->valuesToAssert['emailAddress']
        );

        $this->assertBillingContact($skyLinkBillingContact);
    }

    public function it_builds_a_skylink_shipping_contact_from_the_magento_customer_address(
        AddressInterface $magentoCustomerAddress,
        RegionInterface $magentoCustomerRegion
    ) {
        $this->setupMagentoCustomerAddressStub($magentoCustomerAddress, $magentoCustomerRegion);
        $this->valuesToAssert['emailAddress'] = 'ben@retailexpress.com.au';

        $skyLinkShippingContact = $this->buildSkyLinkShippingContactFromMagentoCustomerAddress(
            $magentoCustomerAddress,
            $this->valuesToAssert['emailAddress']
        );

        $this->assertShippingContact($skyLinkShippingContact);
    }

    public function it_builds_a_skylink_billing_contact_from_the_magento_order_address(
        OrderAddressInterface $magentoOrderAddress
    ) {
        $this->setupMagentoOrderAddressStub($magentoOrderAddress);
        $magentoOrderAddress->getEmail()->willReturn(
            $this->valuesToAssert['emailAddress'] = 'ben@retailexpress.com.au'
        );

        $skyLinkBillingContact = $this->buildSkyLinkBillingContactFromMagentoOrderAddress(
            $magentoOrderAddress
        );

        $this->assertBillingContact($skyLinkBillingContact);
    }

    public function it_builds_a_skylink_shipping_contact_from_the_magento_order_address(
        OrderAddressInterface $magentoOrderAddress
    ) {
        $this->setupMagentoOrderAddressStub($magentoOrderAddress);
        $magentoOrderAddress->getEmail()->willReturn(
            $this->valuesToAssert['emailAddress'] = 'ben@retailexpress.com.au'
        );

        $skyLinkShippingContact = $this->buildSkyLinkShippingContactFromMagentoOrderAddress(
            $magentoOrderAddress
        );

        $this->assertShippingContact($skyLinkShippingContact);
    }

    private function setupMagentoCustomerAddressStub(
        AddressInterface $magentoCustomerAddress,
        RegionInterface $magentoCustomerRegion
    ) {
        $this->setupMagentoAddressStub($magentoCustomerAddress);
        $magentoCustomerAddress->getRegion()->willReturn($magentoCustomerRegion);
        $magentoCustomerRegion->getRegionCode()->willReturn($this->valuesToAssert['addressState'] = 'QLD');
    }

    private function setupMagentoOrderAddressStub(
        OrderAddressInterface $magentoOrderAddress
    ) {
        $this->setupMagentoAddressStub($magentoOrderAddress);
        $magentoOrderAddress->getRegionCode()->willReturn($this->valuesToAssert['addressState'] = 'QLD');
    }

    private function setupMagentoAddressStub($magentoAddress)
    {
        $magentoAddress->getFirstname()->willReturn($this->valuesToAssert['firstName'] = 'Ben');
        $magentoAddress->getLastname()->willReturn($this->valuesToAssert['lastName'] = 'Corlett');
        $magentoAddress->getCompany()->willReturn($this->valuesToAssert['companyName'] = 'Retail Express');
        $magentoAddress->getStreet()->willReturn([
            $this->valuesToAssert['addressLine1'] = 'Unit 5',
            $this->valuesToAssert['addressLine2'] = '192 Ann Street'
        ]);
        $magentoAddress->getCity()->willReturn($this->valuesToAssert['addressCity'] = 'Brisbane');
        $magentoAddress->getPostcode()->willReturn($this->valuesToAssert['addressPostcode'] = '4000');
        $magentoAddress->getCountryId()->willReturn($this->valuesToAssert['addressCountry'] = 'AU');
        $magentoAddress->getTelephone()->willReturn(
            $this->valuesToAssert['phoneNumber'] = '(07) 1111 1111'
        );
        $magentoAddress->getFax()->willReturn(
            $this->valuesToAssert['faxNumber'] = '(07) 2222 2222'
        );
    }

    private function assertBillingContact($skyLinkBillingContact)
    {
        $skyLinkBillingContact->shouldBeAnInstanceOf(SkyLinkBillingContact::class);
        $this->assertContact($skyLinkBillingContact);
        $skyLinkBillingContact->getEmailAddress()->toNative()->shouldBe($this->valuesToAssert['emailAddress']);
        $skyLinkBillingContact->getFaxNumber()->toNative()->shouldBe($this->valuesToAssert['faxNumber']);
    }

    private function assertShippingContact($skyLinkShippingContact)
    {
        $skyLinkShippingContact->shouldBeAnInstanceOf(SkyLinkShippingContact::class);
        $this->assertContact($skyLinkShippingContact);
    }

    private function assertContact($skyLinkContact)
    {
        $skyLinkContact->getName()->getFirstName()->toNative()->shouldBe($this->valuesToAssert['firstName']);
        $skyLinkContact->getName()->getLastName()->toNative()->shouldBe($this->valuesToAssert['lastName']);
        $skyLinkContact->getCompanyName()->toNative()->shouldBe($this->valuesToAssert['companyName']);
        $skyLinkContact->getAddress()->getLine1()->toNative()->shouldBe($this->valuesToAssert['addressLine1']);
        $skyLinkContact->getAddress()->getLine2()->toNative()->shouldBe($this->valuesToAssert['addressLine2']);
        $skyLinkContact->getAddress()->getCity()->toNative()->shouldBe($this->valuesToAssert['addressCity']);
        $skyLinkContact->getAddress()->getState()->toNative()->shouldBe($this->valuesToAssert['addressState']);
        $skyLinkContact->getAddress()->getPostcode()->toNative()->shouldBe($this->valuesToAssert['addressPostcode']);
        $skyLinkContact->getAddress()->getCountry()->getCode()->toNative()->shouldBe($this->valuesToAssert['addressCountry']);
        $skyLinkContact->getPhoneNumber()->toNative()->shouldBe($this->valuesToAssert['phoneNumber']);
    }
}
