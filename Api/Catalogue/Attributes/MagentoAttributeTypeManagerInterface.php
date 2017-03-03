<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType;

interface MagentoAttributeTypeManagerInterface
{
    /**
     * Generates a Search Criteria Builder for the given Magento Attribute Type in order
     * to find corresponding Magento Attributes.
     *
     * @param MagentoAttributeType $magentoAttributeType
     *
     * @return \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder(MagentoAttributeType $magentoAttributeType);

    /**
     * Get the type of attribute that a given Magento Attribute is.
     *
     * @param ProductAttributeInterface $magentoAttribute
     *
     * @return MagentoAttributeType
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Products\UnsupportedAttributeTypeException
     */
    public function getType(ProductAttributeInterface $magentoAttribute);
}
