<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

interface MagentoConfigurableProductRepositoryInterface
{
    /**
     * Finds an existing configurable product based on the given SkyLink Product IDs of it's potential children.
     *
     * @param \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId[] $skyLinkProductId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function findBySkyLinkProductIds(array $skyLinkProductIds);
}
