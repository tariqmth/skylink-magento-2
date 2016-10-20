<?php

namespace RetailExpress\SkyLink\Api\Products;

use RetailExpress\SkyLink\Catalogue\Products\ProductId as SkyLinkProductId;

interface MagentoProductRepositoryInterface
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
    public function findSimpleProductBySkyLinkProductId(SkyLinkProductId $skyLinkProductId);

    /**
     * Finds a configurable product that could represent the given SkyLink Product IDs.
     *
     * @param SkyLinkProductId[] $skyLinkProductIds
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function findConfigurableProductBySkyLinkProductIds(array $skyLinkProductIds);
}
