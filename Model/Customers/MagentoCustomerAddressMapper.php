<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Illuminate\Support\Str;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\RegionInformationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerAddressMapperInterface;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;
use ValueObjects\StringLiteral\StringLiteral;

class MagentoCustomerAddressMapper implements MagentoCustomerAddressMapperInterface
{
    private $magentoCustomerRegionFactory;

    private $countryInformationAcquirer;

    public function __construct(
        RegionInterfaceFactory $magentoCustomerRegionFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirer
    ) {
        $this->magentoCustomerRegionFactory = $magentoCustomerRegionFactory;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    public function mapBillingAddress(
        AddressInterface $magentoBillingAddress,
        SkyLinkBillingContact $skyLinkBillingContact
    ) {
        $this->mapCommonAddressInfo($magentoBillingAddress, $skyLinkBillingContact);

        $magentoBillingAddress->setFax((string) $skyLinkBillingContact->getFaxNumber());
    }

    public function mapShippingAddress(
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
        $state = $skyLinkContact->getAddress()->getState();

        // If we have neither a country or a state, no need to continue
        if (null === $country && null === $state) {
            return;
        }

        // If there's a country available, we'll save that
        if (null !== $country) {
            $magentoAddress->setCountryId((string) $country->getCode());
        }

        // If there's a state, but no country, we'll just save an adhoc region based on the state
        if (null !== $state && null === $country) {
            return $this->mapAdhocRegion($magentoAddress, $state);
        }

        // To get this far, we've got both a country and a state. We'll check in Magento's
        // directory to see if we need to provide a region ID or just an adhoc region.
        try {
            /* @var \Magento\Directory\Api\Data\CountryInformationInterface $countryInformation */
            $countryInformation = $this->countryInformationAcquirer->getCountryInfo($country->getCode());
        } catch (NoSuchEntityException $e) {

            // If there's no country information, we'll just map an ad-hoc region
            if (null !== $state) {
                $this->mapAdhocRegion($magentoAddress, $state);
            }

            return;
        }

        /* @var RegionInformationInterface[]|null $magentoRegions* */
        $magentoRegions = $countryInformation->getAvailableRegions();

        // If there's no regions available, we'll just make an ad-hoc one
        if (null === $magentoRegions) {
            return $this->mapAdhocRegion($magentoAddress, $state);
        }

        /* @var RegionInformationInterface|null $matchingRegion */
        $matchingRegion = $this->findMatchingRegionForState($magentoRegions, $state);

        // If we got this far (having a country with regions) but we couldn't match up
        // any regions, we will leave it blank (given that this country requires them).
        // This may cause validation to fail, but that's then the customer's
        // problem to resolve this.
        if (null === $matchingRegion) {
            return;
        }

        $this->mapRegion($magentoAddress, $matchingRegion);
    }

    private function mapAdhocRegion(AddressInterface $magentoAddress, StringLiteral $state)
    {
        /* @var \Magento\Customer\Api\Data\RegionInterface $customerRegion */
        $customerRegion = $this->magentoCustomerRegionFactory->create();
        $customerRegion->setRegion((string) $state);
        $customerRegion->setRegionId('');

        $magentoAddress->setRegionId(0); // @todo should this be null?
        $magentoAddress->setRegion($customerRegion);
    }

    private function mapRegion(AddressInterface $magentoAddress, RegionInformationInterface $magentoRegion)
    {
        /* @var \Magento\Customer\Api\Data\RegionInterface $customerRegion */
        // $customerRegion = $this->magentoCustomerRegionFactory->create();

        // $magentoAddress->setRegion(null);
        $magentoAddress->setRegionId($magentoRegion->getId());

        // $customerRegion->setRegionId($magentoRegion->getId());
        // $customerRegion->setRegionCode($magentoRegion->getCode());

        // $magentoAddress->setRegion($customerRegion);

        // dd($magentoAddress);
    }

    private function findMatchingRegionForState(array $magentoRegions, StringLiteral $state)
    {
        $state = $this->normaliseRegionOrState((string) $state);

        return array_first($magentoRegions, function ($key, RegionInformationInterface $magentoRegion) use ($state) {
            $code = $this->normaliseRegionOrState($magentoRegion->getCode());
            $name = $this->normaliseRegionOrState($magentoRegion->getName());

            return in_array($state, [$code, $name]);
        });
    }

    private function normaliseRegionOrState($regionOrState)
    {
        // Remove all characters that are not alphanumeric
        $regionOrState = preg_replace('/[^\pL\pN]+/u', '', $regionOrState);

        // Cast to lowercase
        return Str::lower($regionOrState);
    }
}
