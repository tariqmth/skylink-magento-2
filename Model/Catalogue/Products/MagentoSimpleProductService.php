<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoStockItemMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoSimpleProductService implements MagentoSimpleProductServiceInterface
{
    use MagentoProductService;

    private $magentoProductMapper;

    private $magentoStockItemMapper;

    private $magentoProductFactory;

    private $magentoStockItemFactory;

    private $magentoStockRegistry;

    public function __construct(
        MagentoProductMapperInterface $magentoProductMapper,
        MagentoStockItemMapperInterface $magentoStockItemMapper,
        ProductInterfaceFactory $magentoProductFactory,
        StockItemInterfaceFactory $magentoStockItemFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        StockRegistryInterface $magentoStockRegistry,
        ProductUrlPathGenerator $productUrlPathGenerator
    ) {
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoStockItemMapper = $magentoStockItemMapper;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->magentoStockItemFactory = $magentoStockItemFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->magentoStockRegistry = $magentoStockRegistry;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoProduct(SkyLinkProduct $skyLinkProduct)
    {
        /* @var ProductInterface $magentoProduct */
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setTypeId(ProductType::TYPE_SIMPLE);

        /* @var StockItemInterface $magentoStockItem */
        $magentoStockItem = $this->magentoStockItemFactory->create();

        $this->mapAndSave($magentoProduct, $magentoStockItem, $skyLinkProduct);

        return $magentoProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        /* @var StockItemInterface $magentoStockItem */
        $magentoStockItem = $this->magentoStockRegistry->getStockItemBySku($magentoProduct->getSku());

        $this->mapAndSave($magentoProduct, $magentoStockItem, $skyLinkProduct);
    }

    private function mapAndSave(
        ProductInterface $magentoProduct,
        StockItemInterface $magentoStockItem,
        SkyLinkProduct $skyLinkProduct
    ) {
        // Map our Product and Stock Item
        $this->magentoProductMapper->mapMagentoProduct($magentoProduct, $skyLinkProduct);
        $this->magentoStockItemMapper->mapStockItem($magentoStockItem, $skyLinkProduct->getInventoryItem());

        $this->save($magentoProduct);

        $this->magentoStockRegistry->updateStockItemBySku($magentoProduct->getSku(), $magentoStockItem);
    }
}
