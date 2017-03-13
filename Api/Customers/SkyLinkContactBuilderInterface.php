<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;

interface SkyLinkContactBuilderInterface
{
    /**
     * Builds a SkyLink Billing Contact from the given Magento Customer Address.
     *
     * @param AddressInterface  $magentoCustomerAddress
     * @param CustomerInterface $magentoCustomer
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\BillingContact
     */
    public function buildSkyLinkBillingContactFromMagentoCustomerAddress(
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoCustomerAddress
    );

    /**
     * Builds a SkyLink Shipping Contact from the given Magento Customer Address.
     *
     * @param AddressInterface $magentoCustomerAddress
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\ShippingContact
     */
    public function buildSkyLinkShippingContactFromMagentoCustomerAddress(AddressInterface $magentoCustomerAddress);

    /**
     * Builds a barebone, empty (as can be) Billing Contact.
     *
     * @param CustomerInterface $magentoCustomer
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\BillingContact
     */
    public function buildEmptyBillingContact(CustomerInterface $magentoCustomer);

    /**
     * Builds a barebone, empty (as can be) Shipping Contact.
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\ShippingContact
     */
    public function buildEmptyShippingContact();
}
