<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix as SkyLinkMatrix;
use RetailExpress\SkyLink\Api\Catalogue\Products\UrlKeyGeneratorInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

class MagentoConfigurableProductService implements MagentoConfigurableProductServiceInterface
{
    private $magentoProductMapper;

    private $magentoProductFactory;

    /**
     * The Base Magento Product Repository instance.
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $baseMagentoProductRepository;

    private $urlKeyGenerator;

    public function __construct(
        MagentoProductMapperInterface $magentoProductMapper,
        ProductInterfaceFactory $magentoProductFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        UrlKeyGeneratorInterface $urlKeyGenerator
    ) {
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->urlKeyGenerator = $urlKeyGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoProduct(SkyLinkMatrix $skyLinkMatrix)
    {
        /* @var ProductInterface $magentoProduct */
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setTypeId(ConfigurableProductType::TYPE_CODE);

        $this->mapProduct($magentoProduct, $skyLinkMatrix);
        $this->setUrlKeyForMappedProduct($magentoProduct);
        $this->save($magentoProduct);

        return $magentoProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkMatrix $skyLinkMatrix)
    {
        $this->mapProduct($magentoProduct, $skyLinkMatrix);
        $this->save($magentoProduct);
    }

    private function mapProduct(ProductInterface $magentoProduct, SkyLinkMatrix $skyLinkMatrix)
    {
        $this->magentoProductMapper->mapMagentoProduct($magentoProduct, $skyLinkMatrix);
    }

    private function setUrlKeyForMappedProduct(ProductInterface $magentoProduct)
    {
        $urlKey = $this->urlKeyGenerator->generateUniqueUrlKeyForMagentoProduct($magentoProduct);
        $magentoProduct->setCustomAttribute('url_key', $urlKey);
    }

    private function save(ProductInterface $magentoProduct)
    {
        $this->baseMagentoProductRepository->save($magentoProduct);
    }
}
