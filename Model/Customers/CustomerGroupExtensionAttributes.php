<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\GroupInterface;

trait CustomerGroupExtensionAttributes
{
    /**
     * @var \Magento\Customer\Api\Data\GroupExtensionFactory
     */
    private $customerGroupExtensionFactory;

    private function getCustomerGroupExtensionAttributes(GroupInterface $magentoCustomerGroup)
    {
        $extendedAttributes = $magentoCustomerGroup->getExtensionAttributes();

        if (null === $extendedAttributes) {

            /* @var \Magento\Customer\Api\Data\GroupExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->customerGroupExtensionFactory->create();
            $magentoCustomerGroup->setExtensionAttributes($extendedAttributes);
        }

        return $extendedAttributes;
    }
}
