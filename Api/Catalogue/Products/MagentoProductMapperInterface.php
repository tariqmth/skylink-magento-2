<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface MagentoProductMapperInterface
{
    /**
     * Map a Magento Product based on the given SkyLink Product.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Products\AttributeNotMappedException
     * @throws \RetailExpress\SkyLink\Exceptions\Products\AttributeOptionNotMappedException
     */
    public function mapMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct);

    /**
     * Map a Magento Product based on what can be mapped/overridden in the current set Store.
     *
     * @param ProductInterface $magentoProduct
     * @param SkyLinkProduct   $skyLinkProduct
     * @param WebsiteInterface $magentoWebsite
     */
    public function mapMagentoProductForWebsite(
        ProductInterface $magentoProduct,
        SkyLinkProduct $skyLinkProduct,
        WebsiteInterface $magentoWebsite
    );
}
