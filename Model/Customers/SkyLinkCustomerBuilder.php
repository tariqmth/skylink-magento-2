<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerBuilderInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkContactBuilderInterface as SkyLinkCustomerContactBuilderInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\NewsletterSubscription as SkyLinkNewsletterSubscription;
use ValueObjects\StringLiteral\StringLiteral;

class SkyLinkCustomerBuilder implements SkyLinkCustomerBuilderInterface
{
    private $skyLinkCustomerContactBuilder;

    public function __construct(SkyLinkCustomerContactBuilderInterface $skyLinkCustomerContactBuilder)
    {
        $this->skyLinkCustomerContactBuilder = $skyLinkCustomerContactBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFromMagentoCustomer(CustomerInterface $magentoCustomer)
    {
        $magentoBillingAddress = array_first(
            $magentoCustomer->getAddresses(),
            function ($key, AddressInterface $address) {
                return $address->isDefaultBilling();
            }
        );

        $magentoShippingAddress = array_first(
            $magentoCustomer->getAddresses(),
            function ($key, AddressInterface $address) use ($magentoCustomer) {
                return $address->isDefaultShipping();
            }
        );

        if (null !== $magentoBillingAddress) {
            $skyLinkBillingContact = $this
                ->skyLinkCustomerContactBuilder
                ->buildSkyLinkBillingContactFromMagentoCustomerAddress($magentoCustomer, $magentoBillingAddress);
        } else {
            $skyLinkBillingContact = $this
                ->skyLinkCustomerContactBuilder
                ->buildEmptyBillingContact($magentoCustomer);
        }

        if (null !== $magentoShippingAddress) {
            $skyLinkShippingContact = $this
                ->skyLinkCustomerContactBuilder
                ->buildSkyLinkShippingContactFromMagentoCustomerAddress($magentoShippingAddress);
        } else {
            $skyLinkShippingContact = $this
                ->skyLinkCustomerContactBuilder
                ->buildEmptyShippingContact();
        }

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
}
