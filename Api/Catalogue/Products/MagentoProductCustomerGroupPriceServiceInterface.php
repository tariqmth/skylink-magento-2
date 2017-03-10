<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PricingStructure as SkyLinkPricingStructure;

interface MagentoProductCustomerGroupPriceServiceInterface
{
    /**
     * Syncs Customer Group Prices to the given Magento Product.
     *
     * @param ProductInterface        $magentoProduct
     * @param SkyLinkPricingStructure $skyLinkPricingStructure
     */
    public function syncCustomerGroupPrices(
        ProductInterface $magentoProduct,
        SkyLinkPricingStructure $skyLinkPricingStructure
    );
}
