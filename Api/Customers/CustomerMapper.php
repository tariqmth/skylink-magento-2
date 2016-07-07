<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Customers\ShippingContact as SkyLinkShippingContact;

interface CustomerMapper
{
    /**
     * Map the given Customer with the information provided in the Retail Express Customer.
     *
     * @param CustomerInterface $customer
     * @param SkyLinkCustomer   $skyLinkCustomer
     */
    public function mapCustomer(CustomerInterface $customer, SkyLinkCustomer $skyLinkCustomer);

    /**
     * Map The given Billing Address from the information provided in the Retail Express Billing Contact.
     *
     * @param AddressInterface      $billingAddress
     * @param SkyLinkBillingContact $retailExpressBillingContact
     */
    public function mapBillingAddress(
        AddressInterface $billingAddress,
        SkyLinkBillingContact $retailExpressBillingContact
    );

    /**
     * Map The given Shipping Address from the information provided in the Retail Express Shipping Contact.
     *
     * @param AddressInterface       $billingAddress
     * @param SkyLinkShippingContact $retailExpressShippingContact
     */
    public function mapShippingAddress(
        AddressInterface $billingAddress,
        SkyLinkShippingContact $retailExpressShippingContact
    );
}
