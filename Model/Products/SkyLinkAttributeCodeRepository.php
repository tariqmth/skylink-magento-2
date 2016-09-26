<?php

namespace RetailExpress\SkyLink\Magento2\Model\Products;

use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode;
use RetailExpress\SkyLink\Magento2\Api\Products\SkyLinkAttributeCodeRepositoryInterface;

class SkyLinkAttributeCodeRepository implements SkyLinkAttributeCodeRepositoryInterface
{
    /**
     * Retrieve a list of all attribute codes available in SkyLink.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode[]
     */
    public function all()
    {
        return array_values(array_map(function ($attributeCodeName) {
            return AttributeCode::get($attributeCodeName);
        }, AttributeCode::getConstants()));
    }
}
