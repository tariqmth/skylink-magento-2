<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

interface SkyLinkContactBuilderInterface
{
    /**
     * Builds a SkyLink Billing Contact from the given Magento Customer Address.
     *
     * @param AddressInterface $magentoCustomerAddress
     * @param string           $emailAddress
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\BillingContact
     */
    public function buildSkyLinkBillingContactFromMagentoCustomerAddress(AddressInterface $magentoCustomerAddress, $emailAddress);

    /**
     * Builds a SkyLink Shipping Contact from the given Magento Customer Address.
     *
     * @param AddressInterface $magentoCustomerAddress
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\ShippingContact
     */
    public function buildSkyLinkShippingContactFromMagentoCustomerAddress(AddressInterface $magentoCustomerAddress);
}
