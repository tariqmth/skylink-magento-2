<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy as SkyLinkMatrixPolicy;

interface MagentoConfigurableProductLinkManagementInterface
{
    /**
     * Links the given children products to the configurable product provided.
     *
     * @param SkyLinkMatrixPolicy $skyLinkMatrixPolicy
     * @param ProductInterface    $parentProduct
     * @param ProductInterface[]  $childrenProducts
     */
    public function linkChildren(
        SkyLinkMatrixPolicy $skyLinkMatrixPolicy,
        ProductInterface $parentProduct,
        array $childrenProducts
    );
}
