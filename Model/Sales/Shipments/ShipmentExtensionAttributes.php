<?php

namespace RetailExpress\SkyLink\Model\Sales\Shipments;

use Magento\Sales\Api\Data\ShipmentInterface;

trait ShipmentExtensionAttributes
{
    /**
     * Shipment Extension Factory.
     *
     * @var \Magento\Sales\Api\Data\ShipmentExtensionFactory
     */
    private $shipmentExtensionFactory;

    /**
     * Get, or create and get, Extension Attributes for the given Magento Shipment.
     *
     * @param ShipmentInterfae $magentoShipment
     *
     * @return \Magento\Sales\Api\Data\ShipmentExtensionInterface
     */
    private function getShipmentExtensionAttributes(ShipmentInterface $magentoShipment)
    {
        $extendedAttributes = $magentoShipment->getExtensionAttributes();

        if (null === $extendedAttributes) {

            /* @var \Magento\Sales\Api\Data\ShipmentExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->shipmentExtensionFactory->create();
            $magentoShipment->setExtensionAttributes($extendedAttributes);
        }

        return $extendedAttributes;
    }
}
