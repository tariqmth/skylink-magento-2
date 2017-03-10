<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductCustomerGroupPriceServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductStockItemMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Api\Catalogue\Products\UrlKeyGeneratorInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Segregation\MagentoStoreEmulatorInterface;
use RetailExpress\SkyLink\Api\Segregation\MagentoWebsiteRepositoryInterface;

class MagentoSimpleProductService implements MagentoSimpleProductServiceInterface
{
    use ProductInterfaceAsserter;

    private $magentoProductMapper;

    private $magentoStockItemMapper;

    private $magentoProductFactory;

    private $magentoStockItemFactory;

    /**
     * The Base Magento Product Repository instance.
     *
     * @var ProductRepositoryInterface
     */
    private $baseMagentoProductRepository;

    private $magentoProductWebsiteLinkRepository;

    private $magentoProductWebsiteLinkFactory;

    private $magentoStockRegistry;

    private $urlKeyGenerator;

    private $magentoWebsiteRepository;

    private $magentoStoreEmulator;

    private $magentoCustomerGroupPriceService;

    public function __construct(
        MagentoProductMapperInterface $magentoProductMapper,
        MagentoSimpleProductStockItemMapperInterface $magentoStockItemMapper,
        ProductInterfaceFactory $magentoProductFactory,
        StockItemInterfaceFactory $magentoStockItemFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        ProductWebsiteLinkRepositoryInterface $magentoProductWebsiteLinkRepository,
        ProductWebsiteLinkInterfaceFactory $magentoProductWebsiteLinkFactory,
        StockRegistryInterface $magentoStockRegistry,
        UrlKeyGeneratorInterface $urlKeyGenerator,
        MagentoWebsiteRepositoryInterface $magentoWebsiteRepository,
        MagentoStoreEmulatorInterface $magentoStoreEmulator,
        MagentoProductCustomerGroupPriceServiceInterface $magentoCustomerGroupPriceService
    ) {
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoStockItemMapper = $magentoStockItemMapper;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->magentoStockItemFactory = $magentoStockItemFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->magentoProductWebsiteLinkRepository = $magentoProductWebsiteLinkRepository;
        $this->magentoProductWebsiteLinkFactory = $magentoProductWebsiteLinkFactory;
        $this->magentoStockRegistry = $magentoStockRegistry;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->magentoWebsiteRepository = $magentoWebsiteRepository;
        $this->magentoStoreEmulator = $magentoStoreEmulator;
        $this->magentoCustomerGroupPriceService = $magentoCustomerGroupPriceService;
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

        $this->mapProduct($magentoProduct, $skyLinkProduct);
        $this->setUrlKeyForMappedProduct($magentoProduct);
        $this->mapStockAndSave($magentoProduct, $magentoStockItem, $skyLinkProduct);

        return $magentoProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        /* @var StockItemInterface $magentoStockItem */
        $magentoStockItem = $this->magentoStockRegistry->getStockItemBySku($magentoProduct->getSku());

        $this->mapProduct($magentoProduct, $skyLinkProduct);
        $this->mapStockAndSave($magentoProduct, $magentoStockItem, $skyLinkProduct);

        return $magentoProduct;
    }

    private function mapProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $this->magentoProductMapper->mapMagentoProduct($magentoProduct, $skyLinkProduct);
    }

    private function setUrlKeyForMappedProduct(ProductInterface $magentoProduct)
    {
        $urlKey = $this->urlKeyGenerator->generateUniqueUrlKeyForMagentoProduct($magentoProduct);
        $magentoProduct->setCustomAttribute('url_key', $urlKey);
    }

    private function mapStockAndSave(
        ProductInterface &$magentoProduct,
        StockItemInterface $magentoStockItem,
        SkyLinkProduct $skyLinkProduct
    ) {
        $this->magentoStockItemMapper->mapStockItem($magentoStockItem, $skyLinkProduct->getInventoryItem());
        $magentoProduct = $this->baseMagentoProductRepository->save($magentoProduct);
        $this->magentoStockRegistry->updateStockItemBySku($magentoProduct->getSku(), $magentoStockItem);

        $this->syncCustomerGroupPrices($magentoProduct, $skyLinkProduct);
    }

    private function syncCustomerGroupPrices(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        // Loop through Magento Websites that are configured for the global scope,
        // shoot into the website scope and sync customer group prices for that
        // website. Customer Group Prices can't be both global and scoped,
        // so we just scope them all instead. Much simpler ;) NOT!
        array_map(function (WebsiteInterface $magentoWebsite) use ($magentoProduct, $skyLinkProduct) {
            $this->magentoStoreEmulator->onWebsite($magentoWebsite, function () use ($magentoProduct, $skyLinkProduct) {
                $this->magentoCustomerGroupPriceService->syncCustomerGroupPrices(
                    $magentoProduct,
                    $skyLinkProduct->getPricingStructure()
                );
            });
        }, $this->magentoWebsiteRepository->getListFilteredByGlobalSalesChannelId());
    }
}
