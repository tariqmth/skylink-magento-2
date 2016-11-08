<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix as SkyLinkMatrix;

interface MagentoConfigurableProductServiceInterface
{
    /**
     * Create a new Magento Product based on the given SkyLink Product.
     *
     * @param SkyLinkMatrix      $skyLinkMatrix
     * @param ProductInterface[] $magentoSimpleProducts Simple products used for creating configurable links
     *
     * @return ProductInterface
     */
    public function createMagentoProduct(SkyLinkMatrix $skyLinkMatrix, array $magentoSimpleProducts);

    /**
     * Update the given Magento Product with the information from the SkyLink Product.
     *
     * @param ProductInterface $magentoConfigurableProduct
     * @param SkyLinkMatrix    $skyLinkMatrix
     */
    public function updateMagentoProduct(ProductInterface $magentoConfigurableProduct, SkyLinkMatrix $skyLinkMatrix);
}
