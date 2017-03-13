<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;

interface MagentoProductWebsiteManagementInterface
{
    /**
     * Overrides the given Magento Product within the context of a SkyLink Product in a Sales Channel Group.
     *
     * @param ProductInterface                           $magentoProduct
     * @param SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
     */
    public function overrideMagentoProductForSalesChannelGroup(
        ProductInterface &$magentoProduct,
        SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
    );

    /**
     * Assigns the given product to the given Magento Websites based
     * on the array of SkyLink Product in Sales Channel Groups.
     *
     * @param ProductInterface                             $magentoProduct
     * @param SkyLinkProductInSalesChannelGroupInterface[] $skyLinkProductInSalesChannelGroups
     */
    public function assignMagentoProductToWebsitesForSalesChannelGroups(
        ProductInterface $magentoProduct,
        array $skyLinkProductInSalesChannelGroups
    );
}
