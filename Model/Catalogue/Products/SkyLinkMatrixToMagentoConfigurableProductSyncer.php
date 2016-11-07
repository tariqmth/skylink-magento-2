<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;

class SkyLinkMatrixToMagentoConfigurableProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    private $simpleProductSyncer;

    public function __construct(SkyLinkSimpleProductToMagentoSimpleProductSyncer $simpleProductSyncer)
    {
        $this->simpleProductSyncer = $simpleProductSyncer;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts(Product $skyLinkMatrix)
    {
        return $skyLinkMatrix instanceof Matrix;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(Product $skyLinkMatrix)
    {
        // Firstly, we'll sync the simple products in the Matrix
        $magentoSimpleProducts = array_map(function (SimpleProduct $skyLinkProduct) {
            return $this->simpleProductSyncer->sync($skyLinkProduct);
        }, $skyLinkMatrix->getProducts());

        // Then, we'll look for a configurable product, if we find one, we'll map
        // it's attributes based on the Matrix given, then we'll override it's
        // associated simple products with whatever is in the given Matrix.

        var_dump($magentoSimpleProducts);
    }
}
