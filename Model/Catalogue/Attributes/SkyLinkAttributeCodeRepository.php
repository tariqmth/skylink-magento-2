<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkAttributeCodeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode;

class SkyLinkAttributeCodeRepository implements SkyLinkAttributeCodeRepositoryInterface
{
    /**
     * Retrieve a list of all attribute codes available in SkyLink.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode[]
     */
    public function getList()
    {
        return array_values(array_map(function ($attributeCodeName) {
            return AttributeCode::get($attributeCodeName);
        }, AttributeCode::getConstants()));
    }
}
