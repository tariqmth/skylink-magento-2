<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

interface SkyLinkAttributeCodeRepositoryInterface
{
    /**
     * Retrieve a list of all attribute codes available in SkyLink.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode[]
     */
    public function getList();
}
