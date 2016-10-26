<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoSimpleProductService implements MagentoSimpleProductServiceInterface
{
    private $magentoProductMapper;

    private $magentoProductFactory;

    private $baseMagentoProductRepository;

    public function __construct(
        MagentoProductMapperInterface $magentoProductMapper,
        ProductInterfaceFactory $magentoProductFactory,
        ProductRepositoryInterface $baseMagentoProductRepository
    ) {
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoProduct(SkyLinkProduct $skyLinkProduct)
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setTypeId(ProductType::TYPE_SIMPLE);

        $this->mapAndSave($magentoProduct, $skyLinkProduct);

        return $magentoProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $this->mapAndSave($magentoProduct, $skyLinkProduct);
    }

    private function mapAndSave(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $this->magentoProductMapper->mapMagentoProduct($magentoProduct, $skyLinkProduct);
        $this->baseMagentoProductRepository->save($magentoProduct);
    }
}
