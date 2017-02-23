<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupExtensionFactory;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroup as SkyLinkPriceGroup;

class MagentoCustomerGroupService implements MagentoCustomerGroupServiceInterface
{
    use CustomerGroupExtensionAttributes;

    private $customerConfig;

    private $magentoCustomerGroupFactory;

    private $baseMagentoCustomerGroupRepository;

    public function __construct(
        ConfigInterface $customerConfig,
        GroupInterfaceFactory $magentoCustomerGroupFactory,
        GroupExtensionFactory $customerGroupExtensionFactory,
        GroupRepositoryInterface $baseMagentoCustomerGroupRepository
    ) {
        $this->customerConfig = $customerConfig;
        $this->magentoCustomerGroupFactory = $magentoCustomerGroupFactory;
        $this->customerGroupExtensionFactory = $customerGroupExtensionFactory;
        $this->baseMagentoCustomerGroupRepository = $baseMagentoCustomerGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoCustomerGroup(SkyLinkPriceGroup $skyLinkPriceGroup)
    {
        /* @var \Magento\Customer\Api\Data\GroupInterface $magentoCustomerGroup */
        $magentoCustomerGroup = $this->magentoCustomerGroupFactory->create();

        // Setup the basic info for the Magento Customer Group
        $magentoCustomerGroup->setCode((string) $skyLinkPriceGroup->getNameWithType());
        $magentoCustomerGroup->setTaxClassId($this->customerConfig->getCustomerGroupTaxClassId());

        // Add our custom attribute
        $extendedAttributes = $this->getCustomerGroupExtensionAttributes($magentoCustomerGroup);
        $extendedAttributes->setSkylinkPriceGroupKey($skyLinkPriceGroup->getKey());

        // Save and return
        $this->baseMagentoCustomerGroupRepository->save($magentoCustomerGroup);

        return $magentoCustomerGroup;
    }
}
