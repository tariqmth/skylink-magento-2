<?php

namespace RetailExpress\SkyLink\Api\Products;

interface MagentoProductLinkRepositoryInterface
{
    /**
     * Get the parent product ID for the given child product.
     *
     * @param string $childProductId
     *
     * @return string
     */
    public function getParentProductId($childProductId);
}
