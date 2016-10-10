<?php

namespace RetailExpress\SkyLink\Commands\Products;

use RetailExpress\SkyLink\Catalogue\Products\Matrix as SkyLinkProductMatrix;
use RetailExpress\SkyLink\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Catalogue\Products\ProductRepository as SkyLinkProductRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;

class SyncSkyLinkProductToMagentoProductHandler
{
    private $skyLinkProductRepository;

    public function __construct(
        SkyLinkProductRepository $skyLinkProductRepository
    ) {
        $this->skyLinkProductRepository = $skyLinkProductRepository;
        $this->salesChannelId = $salesChannelId;
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

        if ($response instanceof SkyLinkProductMatrix) {

        } else {
            $this->syncSkyLinkIndividualProduct($response);
        }
    }

    private function syncSkyLinkIndividualProduct(SkyLinkProduct $skyLinkProduct)
    {
        // 1. Find existing product
        // 2. Update/create
    }

    private function syncSkyLinkProductMatrix(SkyLinkProductMatrix $skyLinkProductMatrix)
    {
        // 1. Find existing configurable product that matches the matrix
        // 2. Create/update
        // 3. Sync individual products from the matrix
        // 4. Associate products
    }
}
