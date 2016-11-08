<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;

class SkyLinkMatrixToMagentoConfigurableProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    private $magentoConfigurableProductRepository;

    private $magentoConfigurableProductService;

    private $magentoConfigurableProductLinkManagement;

    private $simpleProductSyncer;

    public function __construct(
        MagentoConfigurableProductRepositoryInterface $magentoConfigurableProductRepository,
        MagentoConfigurableProductServiceInterface $magentoConfigurableProductService,
        MagentoConfigurableProductLinkManagementInterface $magentoConfigurableProductLinkManagement,
        SkyLinkSimpleProductToMagentoSimpleProductSyncer $simpleProductSyncer
    ) {
        $this->magentoConfigurableProductRepository = $magentoConfigurableProductRepository;
        $this->magentoConfigurableProductService = $magentoConfigurableProductService;
        $this->magentoConfigurableProductLinkManagement = $magentoConfigurableProductLinkManagement;
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
        // Grab our SkyLink product IDs
        $skyLinkProductIds = array_map(function (SimpleProduct $skyLinkProduct) {
            return $skyLinkProduct->getId();
        }, $skyLinkMatrix->getProducts());

        // Attempt to find an existing product using those IDs
        $magentoConfigurableProduct = $this
            ->magentoConfigurableProductRepository
            ->findBySkyLinkProductIds($skyLinkProductIds);

        if (null !== $magentoConfigurableProduct) {
            $this
                ->magentoConfigurableProductService
                ->updateMagentoProduct($magentoConfigurableProduct, $skyLinkMatrix);
        } else {
            $magentoConfigurableProduct = $this
                ->magentoConfigurableProductService
                ->createMagentoProduct($skyLinkMatrix);
        }

        // Now, we'll sync the simple products in the Matrix
        $magentoSimpleProducts = array_map(function (SimpleProduct $skyLinkProduct) {
            return $this->simpleProductSyncer->sync($skyLinkProduct);
        }, $skyLinkMatrix->getProducts());

        // Finally, we'll sync the children of the configurable product with the ones we have now
        $this
            ->magentoConfigurableProductLinkManagement
            ->syncChildren($magentoConfigurableProduct, $magentoSimpleProducts);

        return $magentoConfigurableProduct;
    }
}
