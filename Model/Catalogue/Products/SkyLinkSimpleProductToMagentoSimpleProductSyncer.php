<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;

class SkyLinkSimpleProductToMagentoSimpleProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    private $magentoSimpleProductRepository;

    private $magentoSimpleProductService;

    public function __construct(
        MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository,
        MagentoSimpleProductServiceInterface $magentoSimpleProductService
    ) {
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;
        $this->magentoSimpleProductService = $magentoSimpleProductService;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts(Product $skyLinkProduct)
    {
        return $skyLinkProduct instanceof SimpleProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(Product $skyLinkProduct)
    {
        $magentoProduct = $this->magentoSimpleProductRepository->findBySkyLinkProductId($skyLinkProduct->getId());

        if (null !== $magentoProduct) {
            $this->magentoSimpleProductService->updateMagentoProduct($magentoProduct, $skyLinkProduct);
        } else {
            $magentoProduct = $this->magentoSimpleProductService->createMagentoProduct($skyLinkProduct);
        }

        return $magentoProduct;
    }
}
