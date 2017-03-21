<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

interface MagentoSimpleProductRepositoryInterface
{
    /**
     * Returns an array of all mapped SkyLink Product IDs for all products
     *
     * @return SkyLinkProductId[]
     */
    public function getListOfMappedSkyLinkProductIds();

    /**
     * Finds an existing simple product by the SkyLink Product ID.
     *
     * @param SkyLinkProductId $skyLinkProductId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException
     */
    public function findBySkyLinkProductId(SkyLinkProductId $skyLinkProductId);
}
