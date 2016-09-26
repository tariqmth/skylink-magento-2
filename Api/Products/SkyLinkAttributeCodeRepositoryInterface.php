<?php

namespace RetailExpress\SkyLink\Magento2\Api\Products;

interface SkyLinkAttributeCodeRepositoryInterface
{
    /**
     * Retrieve a list of all attribute codes available in SkyLink.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode[]
     */
    public function all();
}
