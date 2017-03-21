<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\CustomerInterface;

trait CustomerExtensionAttributes
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerExtensionFactory
     */
    private $customerExtensionFactory;

    private function getCustomerExtensionAttributes(CustomerInterface $magentoCustomer)
    {
        $extendedAttributes = $magentoCustomer->getExtensionAttributes();

        if (null === $extendedAttributes) {

            /* @var \Magento\Customer\Api\Data\CustomerExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->customerExtensionFactory->create();
            $magentoCustomer->setExtensionAttributes($extendedAttributes);
        }

        return $extendedAttributes;
    }
}
