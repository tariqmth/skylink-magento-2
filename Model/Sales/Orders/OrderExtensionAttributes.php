<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;

trait OrderExtensionAttributes
{
    /**
     * Order Extension Factory.
     *
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * Get, or create and get, Extension Attributes for the given Magento Order.
     *
     * @param OrderInterfae $magentoOrder
     *
     * @return \Magento\Sales\Api\Data\OrderExtensionInterface
     */
    private function getOrderExtensionAttributes(OrderInterface $magentoOrder)
    {
        $extendedAttributes = $magentoOrder->getExtensionAttributes();

        if (null === $extendedAttributes) {

            /* @var \Magento\Sales\Api\Data\OrderExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->orderExtensionFactory->create();
            $magentoOrder->setExtensionAttributes($extendedAttributes);
        }

        return $extendedAttributes;
    }
}
