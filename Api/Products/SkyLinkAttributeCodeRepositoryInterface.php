<?php

namespace RetailExpress\SkyLink\Magento2\Api\Products;

interface SkyLinkAttributeCodeRepositoryInterface
{
    /**
     * Retrieve a list of all attribute codes available in SkyLink.
     *
     * @return string[]
     */
    public function all();
}
