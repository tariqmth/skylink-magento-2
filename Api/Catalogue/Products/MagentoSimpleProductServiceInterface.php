<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface MagentoSimpleProductServiceInterface
{
    /**
     * Create a new Magento Product based on the given SkyLink Product.
     *
     * @param SkyLinkProduct $skyLinkProduct
     *
     * @return ProductInterface
     */
    public function createMagentoProduct(SkyLinkProduct $skyLinkProduct);

    /**
     * Update the given Magento Product with the information from the SkyLink Product.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct);

    /**
     * Assigns the given product to the given Magento Websites.
     *
     * @param ProductInterface                           $magentoProduct
     * @param \Magento\Store\Api\Data\WebsiteInterface[] $magentoWebsites
     */
    public function assignMagentoProductToWebsites(ProductInterface $magentoProduct, array $magentoWebsites);

    /**
     * Updates the given Magento Product within the context of a SkyLink Product in a Sales Channel Group.
     *
     *
     * @param ProductInterface                           $magentoProduct
     * @param SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
     */
    public function updateMagentoProductForSalesChannelGroup(
        ProductInterface $magentoProduct,
        SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
    );
}
