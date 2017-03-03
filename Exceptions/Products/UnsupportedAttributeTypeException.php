<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\LocalizedException;

class UnsupportedAttributeTypeException extends LocalizedException
{
    /**
     * Create an new Exception with the given Magento Attribute.
     *
     * @param AttributeInterface $magentoAttribute
     *
     * @return UnsupportedAttributeTypeException
     *
     * @codeCoverageIgnore
     */
    public static function withMagentoAttribute(AttributeInterface $magentoAttribute)
    {
        return new self(__(
            'Magento attribute #%1 "%2" is not a supported Magento Attribute type to work with SkyLink.',
            $magentoAttribute->getAttributeId(),
            $magentoAttribute->getAttributeCode()
        ));
    }
}
