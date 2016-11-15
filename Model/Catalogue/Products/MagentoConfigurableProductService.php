<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
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

    private $magentoConfigurableProductLinkManagement;

    public function __construct(
        MagentoProductMapperInterface $magentoProductMapper,
        ProductInterfaceFactory $magentoProductFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        UrlKeyGeneratorInterface $urlKeyGenerator,
        MagentoConfigurableProductLinkManagementInterface $magentoConfigurableProductLinkManagement
    ) {
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->magentoConfigurableProductLinkManagement = $magentoConfigurableProductLinkManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoProduct(SkyLinkMatrix $skyLinkMatrix, array $magentoSimpleProducts)
    {
        /* @var ProductInterface $magentoConfigurableProduct */
        $magentoConfigurableProduct = $this->magentoProductFactory->create();
        $magentoConfigurableProduct->setTypeId(ConfigurableProductType::TYPE_CODE);

        $this->mapProduct($magentoConfigurableProduct, $skyLinkMatrix);
        $this->setUrlKeyForMappedProduct($magentoConfigurableProduct);
        $this->linkSimpleProducts($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
        $this->save($magentoConfigurableProduct);

        return $magentoConfigurableProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoProduct(
        SkyLinkMatrix $skyLinkMatrix,
        ProductInterface $magentoConfigurableProduct,
        array $magentoSimpleProducts
    ) {
        $this->mapProduct($magentoConfigurableProduct, $skyLinkMatrix);
        $this->linkSimpleProducts($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
        $this->save($magentoConfigurableProduct);
    }

    private function mapProduct(ProductInterface $magentoConfigurableProduct, SkyLinkMatrix $skyLinkMatrix)
    {
        $this->magentoProductMapper->mapMagentoProduct($magentoConfigurableProduct, $skyLinkMatrix);
    }

    private function setUrlKeyForMappedProduct(ProductInterface $magentoConfigurableProduct)
    {
        $urlKey = $this->urlKeyGenerator->generateUniqueUrlKeyForMagentoProduct($magentoConfigurableProduct);
        $magentoConfigurableProduct->setCustomAttribute('url_key', $urlKey);
    }

    private function save(ProductInterface $magentoConfigurableProduct)
    {
        $this->baseMagentoProductRepository->save($magentoConfigurableProduct);
    }

    private function linkSimpleProducts(
        SkyLinkMatrix $skyLinkMatrix,
        ProductInterface $magentoConfigurableProduct,
        array $magentoSimpleProducts
    ) {
        $this
            ->magentoConfigurableProductLinkManagement
            ->linkChildren($skyLinkMatrix->getPolicy(), $magentoConfigurableProduct, $magentoSimpleProducts);
    }
}
