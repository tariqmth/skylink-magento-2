<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PricingStructure as SkyLinkPricingStructure;

interface MagentoSimpleProductCustomerGroupPriceServiceInterface
{
    /**
     * Syncs Customer Group PRices to the given Magento Product.
     *
     * @param string                  $magentoProductSku
     * @param SkyLinkPricingStructure $skyLinkPricingStructure
     */
    public function syncCustomerGroupPrices(
        $magentoProductSku,
        SkyLinkPricingStructure $skyLinkPricingStructure
    );
}
