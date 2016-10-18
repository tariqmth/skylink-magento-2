<?php

namespace RetailExpress\SkyLink\Commands\Products;

use RetailExpress\SkyLink\Api\Products\MagentoProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Products\MagentoProductServiceInterface;
use RetailExpress\SkyLink\Api\Products\MagentoConfigurableProductServiceInterface;
use RetailExpress\SkyLink\Catalogue\Products\Matrix as SkyLinkProductMatrix;
use RetailExpress\SkyLink\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Catalogue\Products\ProductRepository as SkyLinkProductRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;

class SyncSkyLinkProductToMagentoProductHandler
{
    private $skyLinkProductRepository;

    private $magentoProductRepository;

    private $magentoProductService;

    private $configurableProductService;

    public function __construct(
        SkyLinkProductRepository $skyLinkProductRepository,
        MagentoProductRepositoryInterface $magentoProductRepository,
        MagentoProductServiceInterface $magentoProductService,
        MagentoConfigurableProductServiceInterface $configurableProductService
    ) {
        $this->skyLinkProductRepository = $skyLinkProductRepository;
        $this->magentoProductRepository = $magentoProductRepository;
        $this->magentoProductService = $magentoProductService;
        $this->configurableProductService = $configurableProductService;
    }

    /**
     * Synchronise a product by firstly grabbing the product from SkyLink and then
     * attempts to match it to an existing Product in Magento, or create a new one.
     *
     * @param SyncSkyLinkProductToMagentoProductCommand $command
     */
    public function handle(SyncSkyLinkProductToMagentoProductCommand $command)
    {
        $productId = new SkyLinkProductId($command->skyLinkProductId);
        $salesChannelId = new SalesChannelId($command->salesChannelId);

        $response = $this->skyLinkProductRepository->find($productId, $salesChannelId);

        // @todo use specification pattern to support more product types (e.g. bundling, configurable, grouped)
        if ($response instanceof SkyLinkProductMatrix) {
            $this->syncSkyLinkProductMatrix($skyLinkProductMatrix);
        } else {
            $this->syncSkyLinkIndividualProduct($response);
        }
    }

    private function syncSkyLinkIndividualProduct(SkyLinkProduct $skyLinkProduct)
    {
        $magentoProduct = $this->magentoProductRepository->findSimpleProductBySkyLinkProductId($skyLinkProduct->getId());

        if (null !== $magentoProduct) {
            $this->magentoProductService->updateMagentoProduct($magentoProduct, $skyLinkProduct);
        } else {
            $magentoProduct = $this->magentoProductService->createMagentoProduct($skyLinkProduct);
        }

        return $magentoProduct;
    }

    private function syncSkyLinkProductMatrix(SkyLinkProductMatrix $skyLinkProductMatrix)
    {
        // Firstly, sync all of the individual products
        $magentoSimpleProducts = array_map(function (SkyLinkProduct $skyLinkProduct) {
            return $this->syncSkyLinkIndividualProduct($skyLinkProduct);
        }, $skyLinkProductMatrix->getProducts());

        // Now, we'll find an existing configurable product based on the SkyLink Product IDs in our matrix, which
        // as a resul tof the previous synchronisation, we now know represent products existing in Magento.
        $magentoConfigurableProduct = $this->magentoProductRepository->findConfigurableProductBySkyLinkProductIds(array_map(function (SkyLinkProduct $skyLinkProduct) {
            return $product->getId();
        }, $skyLinkProductMatrix->getProducts()));

        if (null !== $magentoConfigurableProduct) {
            $this->magentoProductService->updateMagentoProduct($magentoProduct, $skyLinkProductMatrix);
        } else {
            $magentoConfigurableProduct = $this->magentoProductService->createMagentoProduct($skyLinkProductMatrix);
        }

        // Finally, synchronise hte children of the parent configurable producty7h8
        $this->configurableProductService->syncChildren($magentoConfigurableProduct, $magentoSimpleProducts);
    }
}
