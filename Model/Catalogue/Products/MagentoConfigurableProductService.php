<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductStockItemUpdaterInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix as SkyLinkMatrix;
use RetailExpress\SkyLink\Api\Catalogue\Products\UrlKeyGeneratorInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

class MagentoConfigurableProductService implements MagentoConfigurableProductServiceInterface
{
    private $magentoProductMapper;

    private $magentoStockItemUpdater;

    private $magentoProductFactory;

    /**
     * The Base Magento Product Repository instance.
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $baseMagentoProductRepository;

    private $urlKeyGenerator;

    private $magentoConfigurableProductLinkManagement;

    private $magentoStockItemFactory;

    private $magentoStockRegistry;

    public function __construct(
        MagentoProductMapperInterface $magentoProductMapper,
        MagentoConfigurableProductStockItemUpdaterInterface $magentoStockItemUpdater,
        ProductInterfaceFactory $magentoProductFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        UrlKeyGeneratorInterface $urlKeyGenerator,
        MagentoConfigurableProductLinkManagementInterface $magentoConfigurableProductLinkManagement,
        StockItemInterfaceFactory $magentoStockItemFactory,
        StockRegistryInterface $magentoStockRegistry
    ) {
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoStockItemUpdater = $magentoStockItemUpdater;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->magentoConfigurableProductLinkManagement = $magentoConfigurableProductLinkManagement;
        $this->magentoStockItemFactory = $magentoStockItemFactory;
        $this->magentoStockRegistry = $magentoStockRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoProduct(SkyLinkMatrix $skyLinkMatrix, array $magentoSimpleProducts)
    {
        /* @var ProductInterface $magentoConfigurableProduct */
        $magentoConfigurableProduct = $this->magentoProductFactory->create();
        $magentoConfigurableProduct->setTypeId(ConfigurableProductType::TYPE_CODE);

        /* @var StockItemInterface $magentoStockItem */
        $magentoStockItem = $this->magentoStockItemFactory->create();

        $this->mapProduct($magentoConfigurableProduct, $skyLinkMatrix);
        $this->setUrlKeyForMappedProduct($magentoConfigurableProduct);
        $this->linkSimpleProducts($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
        $this->updateStockAndSave($magentoConfigurableProduct, $magentoStockItem);

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
        /* @var StockItemInterface $magentoStockItem */
        $magentoStockItem = $this->magentoStockRegistry->getStockItemBySku($magentoConfigurableProduct->getSku());

        $this->mapProduct($magentoConfigurableProduct, $skyLinkMatrix);
        $this->linkSimpleProducts($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
        $this->updateStockAndSave($magentoConfigurableProduct, $magentoStockItem);
    }

    private function mapProduct(ProductInterface $magentoConfigurableProduct, SkyLinkMatrix $skyLinkMatrix)
    {
        $this->magentoProductMapper->mapMagentoProduct($magentoConfigurableProduct, $skyLinkMatrix);
    }

    private function setUrlKeyForMappedProduct(ProductInterface $magentoConfigurableProduct)
    {
        $urlKey = $this->urlKeyGenerator->generateUniqueUrlKeyForMagentoProduct($magentoConfigurableProduct);
        $magentoConfigurableProduct->unsetData('url_key');
        $magentoConfigurableProduct->setCustomAttribute('url_key', $urlKey);
    }

    private function updateStockAndSave(
        ProductInterface &$magentoConfigurableProduct,
        StockItemInterface $magentoStockItem
    ) {
        if ($originalConfigurableProduct =
            $this->baseMagentoProductRepository->get($magentoConfigurableProduct->getSku())) {
            $magentoConfigurableProduct->setMediaGalleryEntries($originalConfigurableProduct->getMediaGalleryEntries());
        }
        $this->magentoStockItemUpdater->updateStockItem($magentoStockItem);
        $magentoConfigurableProduct = $this->baseMagentoProductRepository->save($magentoConfigurableProduct);
        $this->magentoStockRegistry->updateStockItemBySku($magentoConfigurableProduct->getSku(), $magentoStockItem);
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
