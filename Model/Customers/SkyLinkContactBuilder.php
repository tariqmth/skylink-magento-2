<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkContactBuilderInterface as SkyLinkCustomerContactBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkContactBuilderInterface as SkyLinkOrderContactBuilderInterface;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;

class SkyLinkContactBuilder implements SkyLinkCustomerContactBuilderInterface, SkyLinkOrderContactBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildSkyLinkBillingContactFromMagentoCustomerAddress(
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoCustomerAddress
    ) {
        $arguments = array_merge(
            $this->extractCommonArguments($magentoCustomerAddress),
            [
                'emailAddress' => $magentoCustomer->getEmail(),
                'addressState' => $this->extractStateFromCustomerAddress($magentoCustomerAddress),
                'faxNumber' => $magentoCustomerAddress->getFax(),
            ]
        );

        return $this->buildSkyLinkBillingContact($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function buildSkyLinkShippingContactFromMagentoCustomerAddress(AddressInterface $magentoCustomerAddress)
    {
        $arguments = array_merge(
            $this->extractCommonArguments($magentoCustomerAddress),
            [
                'addressState' => $this->extractStateFromCustomerAddress($magentoCustomerAddress),
            ]
        );

        return $this->buildSkyLinkShippingContact($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEmptyBillingContact(CustomerInterface $magentoCustomer)
    {
        // @todo should this belong in the SDK (and with other classes too - a createEmpty() method?)
        return SkyLinkBillingContact::fromNative(
            $magentoCustomer->getFirstname(),
            $magentoCustomer->getLastname(),
            $magentoCustomer->getEmail()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildEmptyShippingContact()
    {
        // @todo see self::createEmptyBillingContact() notes...
        return SkyLinkShippingContact::fromNative();
    }

    /**
     * {@inheritdoc}
     */
    public function buildSkyLinkBillingContactFromMagentoOrderAddress(OrderAddressInterface $magentoOrderAddress)
    {
        $arguments = array_merge(
            $this->extractCommonArguments($magentoOrderAddress),
            [
                'emailAddress' => $magentoOrderAddress->getEmail(),
                'addressState' => $magentoOrderAddress->getRegionCode(),
                'faxNumber' => $magentoOrderAddress->getFax(),
            ]
        );

        return $this->buildSkyLinkBillingContact($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function buildSkyLinkShippingContactFromMagentoOrderAddress(OrderAddressInterface $magentoOrderAddress)
    {
        $arguments = array_merge(
            $this->extractCommonArguments($magentoOrderAddress),
            [
                'addressState' => $magentoOrderAddress->getRegionCode(),
            ]
        );

        return $this->buildSkyLinkShippingContact($arguments);
    }

    /**
     * Extracts common arguments from all types of given Magento Addresses.
     *
     * @param AddressInterface|OrderAddressInterface $magentoAddress
     *
     * @return array
     */
    private function extractCommonArguments($magentoAddress)
    {
        $lines = $magentoAddress->getStreet() ?: [];

        return [
            'firstName' => $magentoAddress->getFirstname(),
            'lastName' => $magentoAddress->getLastname(),
            'companyName' => $magentoAddress->getCompany(),
            'addressLine1' => array_get($lines, 0),
            'addressLine2' => array_get($lines, 1),
            'addressCity' => $magentoAddress->getCity(),
            'addressPostcode' => $magentoAddress->getPostcode(),
            'addressCountry' => $magentoAddress->getCountryId(),
            'phoneNumber' => $magentoAddress->getTelephone(),
        ];
    }

    /**
     * Extracts the state from a Customer Address.
     *
     * @param AddressInterface $magentoCustomerAddress
     *
     * @return string
     */
    private function extractStateFromCustomerAddress(AddressInterface $magentoCustomerAddress)
    {
        return $magentoCustomerAddress->getRegion()->getRegionCode();
    }

    /**
     * Builds a SkyLink Billing Contact from the given payload of arguments.
     *
     * @param array $arguments
     *
     * @return SkyLinkBillingContact
     */
    private function buildSkyLinkBillingContact(array $arguments)
    {
        $this->castArgumentsForNativeConstruction($arguments);

        return SkyLinkBillingContact::fromNative(
            $arguments['firstName'],
            $arguments['lastName'],
            $arguments['emailAddress'],
            $arguments['companyName'],
            $arguments['addressLine1'],
            $arguments['addressLine2'],
            $arguments['addressCity'],
            $arguments['addressState'],
            $arguments['addressPostcode'],
            $arguments['addressCountry'],
            $arguments['phoneNumber'],
            $arguments['faxNumber']
        );
    }

    /**
     * Builds a SkyLink Shipping Contact from the given payload of arguments.
     *
     * @param array $arguments
     *
     * @return SkyLinkShippingContact
     */
    private function buildSkyLinkShippingContact(array $arguments)
    {
        $this->castArgumentsForNativeConstruction($arguments);

        return SkyLinkShippingContact::fromNative(
            $arguments['firstName'],
            $arguments['lastName'],
            $arguments['companyName'],
            $arguments['addressLine1'],
            $arguments['addressLine2'],
            $arguments['addressCity'],
            $arguments['addressState'],
            $arguments['addressPostcode'],
            $arguments['addressCountry'],
            $arguments['phoneNumber']
        );
    }

    /**
     * Casts arguments as strings in preparation for construction from native arguments.
     *
     * @param array $arguments
     */
    private function castArgumentsForNativeConstruction(array &$arguments)
    {
        array_walk($arguments, function (&$value) {
            $value = (string) $value;
        });
    }
}
