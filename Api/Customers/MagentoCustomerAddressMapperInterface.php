<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;

interface MagentoCustomerAddressMapperInterface
{
    public function mapBillingAddress(
        AddressInterface $magentoBillingAddress,
        SkyLinkBillingContact $skyLinkBillingContact
    );

    public function mapShippingAddress(
        AddressInterface $magentoShippingAddress,
        SkyLinkShippingContact $skyLinkShippingContact
    );
}
