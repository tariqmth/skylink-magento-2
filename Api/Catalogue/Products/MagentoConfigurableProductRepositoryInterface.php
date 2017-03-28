<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use ValueObjects\StringLiteral\StringLiteral;

interface MagentoConfigurableProductRepositoryInterface
{
    /**
     * Finds an existing configurable product based on the given SkyLink Manufacturer SKU.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException
     */
    public function findBySkyLinkManufacturerSku(StringLiteral $skyLinkManufacturerSku);
}
