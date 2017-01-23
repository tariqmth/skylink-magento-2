<?php

namespace RetailExpress\SkyLink\Api\Data\Catalogue\Products;

use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

interface SkyLinkProductInSalesChannelGroupInterface
{
    /**
     * Get the SkyLink Product.
     *
     * @return SkyLinkProduct
     */
    public function getSkyLinkProduct();

    /**
     * Set the SkyLink Product.
     *
     * @param SkyLinkProduct $skyLinkProduct
     */
    public function setSkyLinkProduct(SkyLinkProduct $skyLinkProduct);

    /**
     * Get the Sales Channel Group.
     *
     * @return SalesChannelGroupInterface
     */
    public function getSalesChannelGroup();

    /**
     * Set the Sales Channel Group.
     *
     * @param SalesChannelGroupInterface $salesChannelGroup
     */
    public function setSalesChannelGroup(SalesChannelGroupInterface $salesChannelGroup);
}
