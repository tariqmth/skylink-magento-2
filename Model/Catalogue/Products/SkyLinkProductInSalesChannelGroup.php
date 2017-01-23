<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class SkyLinkProductInSalesChannelGroup implements SkyLinkProductInSalesChannelGroupInterface
{
    private $skyLinkProduct;

    private $salesChannelGroup;

    /**
     * {@inheritdoc}
     */
    public function getSkyLinkProduct()
    {
        return $this->skyLinkProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function setSkyLinkProduct(SkyLinkProduct $skyLinkProduct)
    {
        $this->skyLinkProduct = $skyLinkProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalesChannelGroup()
    {
        return $this->salesChannelGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalesChannelGroup(SalesChannelGroupInterface $salesChannelGroup)
    {
        $this->salesChannelGroup = $salesChannelGroup;
    }
}
