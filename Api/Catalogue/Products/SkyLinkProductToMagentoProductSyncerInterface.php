<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface SkyLinkProductToMagentoProductSyncerInterface
{
    /**
     * Get the name of the syncer (used for debugging purposes)
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if the syncer accepts the given SkyLink Product to sync.
     *
     * @param SkyLinkProduct $skyLinkProduct
     *
     * @return bool
     */
    public function accepts(SkyLinkProduct $skyLinkProduct);

    /**
     * Perform the actual sync of the given SkyLink Product.
     *
     * @param SkyLinkProduct                             $skyLinkProduct  The SkyLink product being synced
     * @param \Magento\Store\Api\Data\WebsiteInterface[] $magentoWebsites Websites to enable the product for
     *
     * @return \
     */
    public function sync(SkyLinkProduct $skyLinkProduct, array $magentoWebsites);

    /**
     * Sync the given Magento Product specifics from the SkyLink Product in a Sales Channel Group.
     *
     * @param ProductInterface                           $magentoProduct
     * @param SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInsalesChannelGroup
     */
    public function syncFromSkyLinkProductInSalesChannelGroup(
        ProductInterface $magentoProduct,
        SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInsalesChannelGroup
    );
}
