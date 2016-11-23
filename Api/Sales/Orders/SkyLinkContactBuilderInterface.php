<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use Magento\Sales\Api\Data\OrderAddressInterface;

interface SkyLinkContactBuilderInterface
{
    /**
     * Builds a SkyLink Billing Contact from the given Magento Order Address.
     *
     * @param OrderAddressInterface $magentoOrderAddress
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\BillingContact
     */
    public function buildSkyLinkBillingContactFromMagentoOrderAddress(OrderAddressInterface $magentoOrderAddress);

    /**
     * Builds a SkyLink Shipping Contact from the given Magento Order Address.
     *
     * @param OrderAddressInterface $magentoOrderAddress
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\ShippingContact
     */
    public function buildSkyLinkShippingContactFromMagentoOrderAddress(OrderAddressInterface $magentoOrderAddress);
}
