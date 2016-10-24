<?php

namespace RetailExpress\SkyLink\Api\Products;

use RetailExpress\SkyLink\Catalogue\Products\ProductId as SkyLinkProductId;

interface MagentoSimpleProductRepositoryInterface
{
    /**
     * Finds an existing simple product by the SkyLink Product ID.
     *
     * @param SkyLinkProductId $skyLinkProductId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException
     */
    public function findProductBySkyLinkProductId(SkyLinkProductId $skyLinkProductId);
}
