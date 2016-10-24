<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

interface SkyLinkProductTypeRepositoryInterface
{
    /**
     * List all Product Types available.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption[]
     */
    public function getList();
}
