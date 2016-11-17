<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerBuilderInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\NewsletterSubscription as SkyLinkNewsletterSubscription;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;
use ValueObjects\StringLiteral\StringLiteral;

class SkyLinkCustomerBuilder implements SkyLinkCustomerBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildFromMagentoCustomer(CustomerInterface $magentoCustomer)
    {
        $magentoBillingAddress = current(array_filter(
            $magentoCustomer->getAddresses(),
            function (AddressInterface $address) {
                return $address->isDefaultBilling();
            }
        ));

        $magentoShippingAddress = current(array_filter(
            $magentoCustomer->getAddresses(),
            function (AddressInterface $address) use ($magentoCustomer) {
                return $address->isDefaultShipping();
            }
        ));

        $skyLinkBillingContact = $this->createBillingContact($magentoBillingAddress, $magentoCustomer->getEmail());
        $skyLinkShippingContact = $this->createShippingContact($magentoBillingAddress);
        $skyLinkNewsletterSubscription = new SkyLinkNewsletterSubscription(false);

        // If the Magento Customer has a SkyLink Customer ID attached to it
        $skyLinkCustomerIdAttribute = $magentoCustomer->getCustomAttribute('skylink_customer_id');
        if (null !== $skyLinkCustomerIdAttribute) {
            $skyLinkCustomerId = new SkyLinkCustomerId($skyLinkCustomerIdAttribute->getValue());

            return SkyLinkCustomer::existing(
                $skyLinkCustomerId,
                $skyLinkBillingContact,
                $skyLinkShippingContact,
                $skyLinkNewsletterSubscription
            );
        }

        return SkyLinkCustomer::register(
            new StringLiteral(str_random(8)), // We don't actually want to integrate passwords here
            $skyLinkBillingContact,
            $skyLinkShippingContact,
            $skyLinkNewsletterSubscription
        );
    }

    private function createBillingContact(AddressInterface $billingAddress, $email)
    {
        return forward_static_call_array(
            [SkyLinkBillingContact::class, 'fromNative'],
            $this->getBillingContactArguments($billingAddress, $email)
        );
    }

    private function createShippingContact(AddressInterface $shippingAddress)
    {
        // We'll strip out the email and fax arguments as these are not used in teh shipping contact
        $arguments = $this->getBillingContactArguments($shippingAddress);
        unset($arguments[2]); // Email
        unset($arguments[11]); // Fax

        return forward_static_call_array([SkyLinkShippingContact::class, 'fromNative'], $arguments);
    }

    private function getBillingContactArguments(AddressInterface $magentoAddress = null, $email = '')
    {
        if (null === $magentoAddress) {
            return array_fill(0, 11, '');
        }

        $addressLines = $magentoAddress->getStreet() ?: [];

        return [
            (string) $magentoAddress->getFirstname(),
            (string) $magentoAddress->getLastname(),
            $email,
            (string) $magentoAddress->getCompany(),
            array_get($addressLines, 0, ''),
            array_get($addressLines, 1, ''),
            (string) $magentoAddress->getCity(),
            $magentoAddress->getRegion() ? $magentoAddress->getRegion()->getRegionCode() : '',
            $magentoAddress->getPostCode(),
            $magentoAddress->getCountryId(),
            $magentoAddress->getTelephone(),
            $magentoAddress->getFax(),
        ];
    }
}
