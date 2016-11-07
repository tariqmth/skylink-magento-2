<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerMapperInterface;

class MagentoCustomerMapper implements MagentoCustomerMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapMagentoCustomer(CustomerInterface $magentoCustomer, SkyLinkCustomer $skyLinkCustomer)
    {
        $magentoBillingAddress = current(array_filter(
            $magentoCustomer->getAddresses(),
            function (AddressInterface $address){
                return $address->isDefaultBilling();
            }
        ));

        $magentoShippingAddress = current(array_filter(
            $magentoCustomer->getAddresses(),
            function (AddressInterface $address) use ($magentoCustomer) {
                return $address->isDefaultShipping();
            }
        ));

        $skyLinkBillingContact = $skyLinkCustomer->getBillingContact();

        $this->mapBasicInfo($magentoCustomer, $skyLinkBillingContact);

        $this->mapBillingAddress(
            $magentoBillingAddress,
            $skyLinkBillingContact
        );

        $this->mapShippingAddress(
            $magentoShippingAddress,
            $skyLinkCustomer->getShippingContact()
        );
    }

    private function mapBasicInfo(CustomerInterface $magentoCustomer, SkyLinkBillingContact $skyLinkBillingContact)
    {
        $magentoCustomer
            ->setFirstname((string) $skyLinkBillingContact->getName()->getFirstName())
            ->setLastname((string) $skyLinkBillingContact->getName()->getLastName())
            ->setEmail((string) $skyLinkBillingContact->getEmailAddress());
    }

    private function mapBillingAddress(AddressInterface $magentoBillingAddress, SkyLinkBillingContact $skyLinkBillingContact)
    {
        $this->mapCommonAddressInfo($magentoBillingAddress, $skyLinkBillingContact);

        $magentoBillingAddress->setFax((string) $skyLinkBillingContact->getFaxNumber());
    }

    private function mapShippingAddress(
        AddressInterface $magentoShippingAddress,
        SkyLinkShippingContact $skyLinkShippingContact
    ) {
        $this->mapCommonAddressInfo($magentoShippingAddress, $skyLinkShippingContact);
    }

    private function mapCommonAddressInfo(AddressInterface $magentoAddress, $skyLinkContact)
    {
        $magentoAddress
            ->setFirstname((string) $skyLinkContact->getName()->getFirstName())
            ->setLastname((string) $skyLinkContact->getName()->getLastName())
            ->setCompany((string) $skyLinkContact->getCompanyName())
            ->setStreet([
                (string) $skyLinkContact->getAddress()->getLine1(),
                (string) $skyLinkContact->getAddress()->getLine2(),
                (string) $skyLinkContact->getAddress()->getLine3(),
            ])
            ->setCity((string) $skyLinkContact->getAddress()->getCity())
            ->setPostcode((string) $skyLinkContact->getAddress()->getPostcode())
            ->setTelephone((string) $skyLinkContact->getPhoneNumber());

        $country = $skyLinkContact->getAddress()->getCountry();
        if (null !== $country) {
            $magentoAddress->setCountryId((string) $country->getCode());
        }
    }
}
