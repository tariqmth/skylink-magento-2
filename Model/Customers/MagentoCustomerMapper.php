<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Model\StoreManagerInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerAddressMapperInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerMapperInterface;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;
use RetailExpress\SkyLink\Exceptions\Customers\CustomerGroupNotSyncedException;
use RetailExpress\SkyLink\Sdk\Customers\BillingContact as SkyLinkBillingContact;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\ShippingContact as SkyLinkShippingContact;

class MagentoCustomerMapper implements MagentoCustomerMapperInterface
{
    use CustomerExtensionAttributes;

    private $customerConfig;

    private $magentoCustomerGroupRepository;

    private $magentoStoreManager;

    private $magentoCustomerAddressMapper;

    public function __construct(
        ConfigInterface $customerConfig,
        MagentoCustomerGroupRepositoryInterface $magentoCustomerGroupRepository,
        CustomerExtensionFactory $customerExtensionFactory,
        StoreManagerInterface $magentoStoreManager,
        MagentoCustomerAddressMapperInterface $magentoCustomerAddressMapper
    ) {
        $this->customerConfig = $customerConfig;
        $this->magentoCustomerGroupRepository = $magentoCustomerGroupRepository;
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->magentoStoreManager = $magentoStoreManager;
        $this->magentoCustomerAddressMapper = $magentoCustomerAddressMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function mapMagentoCustomer(CustomerInterface $magentoCustomer, SkyLinkCustomer $skyLinkCustomer)
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

        $skyLinkBillingContact = $skyLinkCustomer->getBillingContact();

        $this->mapBasicInfo($magentoCustomer, $skyLinkBillingContact);

        $this->assignToDefaultWebsite($magentoCustomer);

        $this->mapCustomerGroup($magentoCustomer, $skyLinkCustomer);

        $this->mapSubscription($magentoCustomer, $skyLinkCustomer);

        $this->magentoCustomerAddressMapper->mapBillingAddress(
            $magentoBillingAddress,
            $skyLinkBillingContact
        );

        // If the customer was created in Magento, likely they have one address for both billing and shipping.
        // Therefore, we don't need to update the shipping address (as it's a slightly less detailed
        // version of the billing address)
        if ($magentoShippingAddress !== $magentoBillingAddress) {
            $this->magentoCustomerAddressMapper->mapShippingAddress(
                $magentoShippingAddress,
                $skyLinkCustomer->getShippingContact()
            );

            // Reattach the addresses
            $magentoCustomer->setAddresses([$magentoBillingAddress, $magentoShippingAddress]);

        // Reattach the new address
        } else {
            $magentoCustomer->setAddresses([$magentoBillingAddress]);
        }
    }

    private function mapBasicInfo(CustomerInterface $magentoCustomer, SkyLinkBillingContact $skyLinkBillingContact)
    {
        $magentoCustomer
            ->setFirstname((string) $skyLinkBillingContact->getName()->getFirstName())
            ->setLastname((string) $skyLinkBillingContact->getName()->getLastName())
            ->setEmail((string) $skyLinkBillingContact->getEmailAddress());
    }

    /**
     * Because we force customers to be shared globally, then we will just assign the customer the default website.
     *
     * @param CustomerInterface $magentoCustomer
     */
    private function assignToDefaultWebsite(CustomerInterface $magentoCustomer)
    {
        $websiteId = $this->magentoStoreManager->getDefaultStoreView()->getWebsiteId();

        $magentoCustomer->setWebsiteId($websiteId);
    }

    private function mapCustomerGroup(CustomerInterface $magentoCustomer, SkyLinkCustomer $skyLinkCustomer)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType $skyLinkPriceGroupType */
        $skyLinkPriceGroupType = $this->customerConfig->getSkyLinkPriceGroupType();

        // If the SkyLink Customer has a Price Group Key for the given Price Group Type, we'll find
        // our own mapping for that and set a property on the Magenot Customer accordingly.
        if (!$skyLinkCustomer->hasPriceGroupKey($skyLinkPriceGroupType)) {
            $magentoCustomer->setGroupId($this->customerConfig->getDefaultCustomerGroupId());

            return;
        }

        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey $skyLinkPriceGroupKey */
        $skyLinkPriceGroupKey = $skyLinkCustomer->getPriceGroupKey($skyLinkPriceGroupType);

        /* @var \Magento\Customer\Api\Data\GroupInterface|null $magentoCustomerGroup */
        $magentoCustomerGroup = $this->magentoCustomerGroupRepository->findBySkyLinkPriceGroupKey($skyLinkPriceGroupKey);

        if (null === $magentoCustomerGroup) {
            throw CustomerGroupNotSyncedException::withSkyLinkPriceGroupKey($skyLinkPriceGroupKey);
        }

        $magentoCustomer->setGroupId($magentoCustomerGroup->getId());
    }

    private function mapSubscription(CustomerInterface $magentoCustomer, SkyLinkCustomer $skyLinkCustomer)
    {
        /* @var \Magento\Customer\Api\Data\CustomerExtensionInterface $extendedAttributes */
        $extendedAttributes = $this->getCustomerExtensionAttributes($magentoCustomer);

        $extendedAttributes->setIsSubscribed(
            $skyLinkCustomer->getNewsletterSubscription()->toNative()
        );
    }
}
