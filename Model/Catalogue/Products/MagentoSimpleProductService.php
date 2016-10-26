<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;

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
        $magentoProduct = $this->createMagentoProduct();

        $this->mapAndSave($magentoProduct);

        return $magentoProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $this->mapAndSave($magentoProduct);
    }

    private function createMagentoProduct()
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setTypeId(ProductType::TYPE_SIMPLE);

        return $magentoProduct;
    }

    private function mapAndSave(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $this->magentoProductMapper->map($magentoProduct, $skyLinkProduct);
        $this->baseMagentoProductRepository->save($magentoProduct);
    }
}
