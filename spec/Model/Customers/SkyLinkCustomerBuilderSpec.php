<?php

namespace spec\RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\Api\AttributeInterface;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Api\Customers\SkyLinkContactBuilderInterface as SkyLinkCustomerContactBuilderInterface;
use RetailExpress\SkyLink\Model\Customers\SkyLinkCustomerBuilder;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;

class SkyLinkCustomerBuilderSpec extends ObjectBehavior
{
    private $skyLinkCustomerContactBuilder;

    public function let(
        SkyLinkCustomerContactBuilderInterface $skyLinkCustomerContactBuilder
    ) {
        $this->skyLinkCustomerContactBuilder = $skyLinkCustomerContactBuilder;

        $this->beConstructedWith($this->skyLinkCustomerContactBuilder);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SkyLinkCustomerBuilder::class);
    }

    public function it_builds_correctly_for_an_existing_customer(
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoBillingAddress,
        AddressInterface $magentoShippingAddress,
        SkyLinkBillingContact $skyLinkBillingContact,
        SkyLinkShippingContact $SkyLinkShippingContact,
        AttributeInterface $skyLinkCustomerIdAttribute
    ) {
        // Stubbing which address is billing and shipping
        $magentoCustomer->getAddresses()->willReturn([$magentoBillingAddress, $magentoShippingAddress]);
        $magentoBillingAddress->isDefaultBilling()->willReturn(true);
        $magentoBillingAddress->isDefaultShipping()->willReturn(false);
        $magentoShippingAddress->isDefaultShipping()->willReturn(true);

        // Customer's email
        $magentoCustomer->getEmail()->willReturn($billingContactEmail = 'ben@retailexpress.com.au');

        // Creating SkyLink contacts
        $this
            ->skyLinkCustomerContactBuilder
            ->buildSkyLinkBillingContactFromMagentoCustomerAddress($magentoBillingAddress, $billingContactEmail)
            ->willReturn($skyLinkBillingContact);
        $this
            ->skyLinkCustomerContactBuilder
            ->buildSkyLinkShippingContactFromMagentoCustomerAddress($magentoShippingAddress)
            ->willReturn($SkyLinkShippingContact);

        // Deal with existing customer
        $magentoCustomer->getCustomAttribute('skylink_customer_id')->willReturn($skyLinkCustomerIdAttribute);
        $skyLinkCustomerIdAttribute->getValue()->willReturn($skyLinkCustomerId = 300000);

        // Build the SkyLink Customer
        $skyLinkCustomer = $this->buildFromMagentoCustomer($magentoCustomer);

        $skyLinkCustomer->getId()->toNative()->shouldBe($skyLinkCustomerId);
        $skyLinkCustomer->getPassword()->shouldBe(null);
    }
}
