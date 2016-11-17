<?php

namespace spec\RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\Api\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Model\Customers\SkyLinkCustomerBuilder;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

class SkyLinkCustomerBuilderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkCustomerBuilder::class);
    }

    public function it_builds_correctly_for_an_existing_customer(
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoBillingAddress,
        AddressInterface $magentoShippingAddress,
        RegionInterface $contactAddressRegion,
        AttributeInterface $skyLinkCustomerIdAttribute
    ) {
        $valuesToAssert = $this->setupMagentoCustomerStubs(
            $magentoCustomer,
            $magentoBillingAddress,
            $magentoShippingAddress,
            $contactAddressRegion
        );

        // Deal with existing customer
        $magentoCustomer->getCustomAttribute('skylink_customer_id')->willReturn($skyLinkCustomerIdAttribute);
        $skyLinkCustomerIdAttribute->getValue()->willReturn($skyLinkCustomerId = 124001);

        $skyLinkCustomer = $this->buildFromMagentoCustomer($magentoCustomer);

        $skyLinkCustomer->getId()->toNative()->shouldBe($skyLinkCustomerId);
        $skyLinkCustomer->getPassword()->shouldBe(null);

        // Assert stubbed values
        $this->assertStubbedValuesAgainstSkyLinkCustomer($skyLinkCustomer, $valuesToAssert);
    }

    public function it_builds_correctly_for_a_new_customer(
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoBillingAddress,
        AddressInterface $magentoShippingAddress,
        RegionInterface $contactAddressRegion
    ) {
        $valuesToAssert = $this->setupMagentoCustomerStubs(
            $magentoCustomer,
            $magentoBillingAddress,
            $magentoShippingAddress,
            $contactAddressRegion
        );

        // Deal with existing customer
        $magentoCustomer->getCustomAttribute('skylink_customer_id')->willReturn(null);

        $skyLinkCustomer = $this->buildFromMagentoCustomer($magentoCustomer);

        $skyLinkCustomer->getId()->shouldBe(null);
        $skyLinkCustomer->getPassword()->toNative()->shouldMatch('/[a-zA-Z0-9]{8}/'); // 8 letter alpha-numeric

        // Assert stubbed values
        $this->assertStubbedValuesAgainstSkyLinkCustomer($skyLinkCustomer, $valuesToAssert);
    }

    private function setupMagentoCustomerStubs(
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoBillingAddress,
        AddressInterface $magentoShippingAddress,
        RegionInterface $contactAddressRegion
    ) {
        $magentoCustomer->getAddresses()->willReturn([$magentoBillingAddress, $magentoShippingAddress]);
        $magentoCustomer->getEmail()->willReturn($billingContactEmail = 'ben@retailexpress.com.au');

        $magentoBillingAddress->isDefaultBilling()->willReturn(true);
        $magentoBillingAddress->isDefaultShipping()->willReturn(false);
        $magentoBillingAddress->getFirstname()->willReturn($contactFirstName = 'Ben');
        $magentoBillingAddress->getLastname()->willReturn($contactLastName = 'Corlett');
        $magentoBillingAddress->getCompany()->willReturn($contactCompany = 'Retail Express');
        $magentoBillingAddress->getStreet()->willReturn([
            $contactAddressLine1 = 'Unit 5',
            $contactAddressLine2 = '192 Ann Street',
        ]);
        $magentoBillingAddress->getCity()->willReturn($contactAddressCity = 'Brisbane');
        $magentoBillingAddress->getRegion()->willReturn($contactAddressRegion);
        $contactAddressRegion->getRegionCode()->willReturn($contactAddressState = 'QLD');
        $magentoBillingAddress->getPostcode()->willReturn($contactAddressPostcode = '4000');
        $magentoBillingAddress->getCountryId()->willReturn($contactAddressCountry = 'AU');
        $magentoBillingAddress->getTelephone()->willReturn($contactAddressPhoneNumber = '(07) 1111 1111');
        $magentoBillingAddress->getFax()->willReturn($billingContactAddressFaxNumber = '(07) 1111 2222');

        $magentoShippingAddress->isDefaultShipping()->willReturn(true);
        $magentoShippingAddress->isDefaultBilling()->willReturn(false);

        $magentoShippingAddress->getFirstname()->willReturn($contactFirstName = 'Ben');
        $magentoShippingAddress->getLastname()->willReturn($contactLastName = 'Corlett');
        $magentoShippingAddress->getCompany()->willReturn($contactCompany = 'Retail Express');
        $magentoShippingAddress->getStreet()->willReturn([
            $contactAddressLine1 = 'Unit 5',
            $contactAddressLine2 = '192 Ann Street',
        ]);
        $magentoShippingAddress->getCity()->willReturn($contactAddressCity = 'Brisbane');
        $magentoShippingAddress->getRegion()->willReturn($contactAddressRegion);
        $magentoShippingAddress->getPostcode()->willReturn($contactAddressPostcode = '4000');
        $magentoShippingAddress->getCountryId()->willReturn($contactAddressCountry = 'AU');
        $magentoShippingAddress->getTelephone()->willReturn($contactAddressPhoneNumber = '(07) 1111 1111');

        return compact(
            'billingContactEmail',
            'contactFirstName',
            'contactLastName',
            'contactCompany',
            'contactAddressLine1',
            'contactAddressLine2',
            'contactAddressCity',
            'contactAddressRegion',
            'contactAddressState',
            'contactAddressPostcode',
            'contactAddressCountry',
            'contactAddressPhoneNumber',
            'billingContactAddressFaxNumber'
        );
    }

    private function assertStubbedValuesAgainstSkyLinkCustomer($skyLinkCustomer, array $valuesToAssert)
    {
        extract($valuesToAssert);

        $skyLinkBillingContact = $skyLinkCustomer->getBillingContact();
        $skyLinkBillingContact->getEmailAddress()->toNative()->shouldBe($billingContactEmail);
        $skyLinkBillingContact->getName()->getFirstName()->toNative()->shouldBe($contactFirstName);
        $skyLinkBillingContact->getName()->getLastName()->toNative()->shouldBe($contactLastName);
        $skyLinkBillingContact->getCompanyName()->toNative()->shouldBe($contactCompany);
        $skyLinkBillingContact->getAddress()->getLine1()->toNative()->shouldBe($contactAddressLine1);
        $skyLinkBillingContact->getAddress()->getLine2()->toNative()->shouldBe($contactAddressLine2);
        $skyLinkBillingContact->getAddress()->getCity()->toNative()->shouldBe($contactAddressCity);
        $skyLinkBillingContact->getAddress()->getState()->toNative()->shouldBe($contactAddressState);
        $skyLinkBillingContact->getAddress()->getPostcode()->toNative()->shouldBe($contactAddressPostcode);
        $skyLinkBillingContact->getAddress()->getCountry()->getCode()->toNative()->shouldBe($contactAddressCountry);
        $skyLinkBillingContact->getPhoneNumber()->toNative()->shouldBe($contactAddressPhoneNumber);
        $skyLinkBillingContact->getFaxNumber()->toNative()->shouldBe($billingContactAddressFaxNumber);

        $skyLinkShippingContact = $skyLinkCustomer->getShippingContact();
        $skyLinkShippingContact->getName()->getFirstName()->toNative()->shouldBe($contactFirstName);
        $skyLinkShippingContact->getName()->getLastName()->toNative()->shouldBe($contactLastName);
        $skyLinkShippingContact->getCompanyName()->toNative()->shouldBe($contactCompany);
        $skyLinkShippingContact->getAddress()->getLine1()->toNative()->shouldBe($contactAddressLine1);
        $skyLinkShippingContact->getAddress()->getLine2()->toNative()->shouldBe($contactAddressLine2);
        $skyLinkShippingContact->getAddress()->getCity()->toNative()->shouldBe($contactAddressCity);
        $skyLinkShippingContact->getAddress()->getState()->toNative()->shouldBe($contactAddressState);
        $skyLinkShippingContact->getAddress()->getPostcode()->toNative()->shouldBe($contactAddressPostcode);
        $skyLinkShippingContact->getAddress()->getCountry()->getCode()->toNative()->shouldBe($contactAddressCountry);
        $skyLinkShippingContact->getPhoneNumber()->toNative()->shouldBe($contactAddressPhoneNumber);
    }
}
