<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class ExistingMagentoProductRequiredToSyncSkyLinkInventoryItemToStockItemException extends LocalizedException
{
    public static function withSkyLinkProductId(SkyLinkProductId $skyLinkProductId)
    {
        return new self(__(
            'Cannot sync the Inventory Item for SkyLink Product #%1 to Magento because the SkyLink Product itself has not been synced yet.',
            $skyLinkProductId
        ));
    }
}
