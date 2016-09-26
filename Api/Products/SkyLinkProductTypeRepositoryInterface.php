<?php

namespace RetailExpress\SkyLink\Magento2\Api\Products;

interface SkyLinkProductTypeRepositoryInterface
{
    /**
     * List all Product Types available.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption[]
     */
    public function getList();
}
