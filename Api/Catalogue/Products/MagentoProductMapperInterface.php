<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface MagentoProductMapperInterface
{
    /**
     * Map a Magento Product based on the given SkyLink Product.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     */
    public function mapMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct);

    /**
     * Maps the Magento Product based on the given SkyLink Product in a Sales Channel Group.
     *
     * This method is the entry point to scoped data for stores/websites, which can
     * be accessed through the Sales Channel Group object.
     *
     * @param ProductInterface                           $magentoProduct
     * @param SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
     */
    public function mapMagentoProductForSalesChannelGroup(
        ProductInterface $magentoProduct,
        SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
    );
}
