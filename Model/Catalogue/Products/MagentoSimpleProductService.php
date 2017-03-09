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
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductCustomerGroupPriceServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductStockItemMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Api\Catalogue\Products\UrlKeyGeneratorInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;

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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $baseMagentoProductRepository;

    private $magentoProductWebsiteLinkRepository;

    private $magentoProductWebsiteLinkFactory;

    private $magentoStockRegistry;

    private $urlKeyGenerator;

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
        MagentoSimpleProductCustomerGroupPriceServiceInterface $magentoCustomerGroupPriceService
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
    }

    /**
     * {@inheritdoc}
     */
    public function assignMagentoProductToWebsites(ProductInterface $magentoProduct, array $magentoWebsites)
    {
        $this->assertImplementationOfProductInterface($magentoProduct);

        $existingIds = array_map('intval', $magentoProduct->getWebsiteIds());
        $newIds = array_map(function (WebsiteInterface $magentoWebsite) {
            return (int) $magentoWebsite->getId();
        }, $magentoWebsites);

        // Determine the IDs to remove and add
        $idsToRemove = array_diff($existingIds, $newIds);
        $idsToAdd = array_diff($newIds, $existingIds);

        // Remove from websites
        array_walk($idsToRemove, function ($idToRemove) use ($magentoProduct) {
            $result = $this->magentoProductWebsiteLinkRepository->deleteById(
                $magentoProduct->getSku(),
                $idToRemove
            );
        });

        // Add to websites
        array_walk($idsToAdd, function ($idToAdd) use ($magentoProduct) {
            $magentoProductWebsiteLink = $this->magentoProductWebsiteLinkFactory->create();
            $magentoProductWebsiteLink
                ->setSku($magentoProduct->getSku())
                ->setWebsiteId($idToAdd);

            $this->magentoProductWebsiteLinkRepository->save($magentoProductWebsiteLink);
        });

        $this->saveDirectly($magentoProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateMagentoProductForSalesChannelGroup(
        ProductInterface $magentoProduct,
        SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
    ) {
        $this->magentoProductMapper->mapMagentoProductForSalesChannelGroup(
            $magentoProduct,
            $skyLinkProductInSalesChannelGroup
        );

        $this->save($magentoProduct);
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
        ProductInterface $magentoProduct,
        StockItemInterface $magentoStockItem,
        SkyLinkProduct $skyLinkProduct
    ) {
        $magentoProductSku = $magentoProduct->getSku();

        $this->magentoStockItemMapper->mapStockItem($magentoStockItem, $skyLinkProduct->getInventoryItem());
        $this->save($magentoProduct);
        $this->magentoStockRegistry->updateStockItemBySku($magentoProductSku, $magentoStockItem);
        $this->magentoCustomerGroupPriceService->syncCustomerGroupPrices($magentoProductSku, $skyLinkProduct->getPricingStructure());
    }

    private function save(ProductInterface $magentoProduct)
    {
        $this->baseMagentoProductRepository->save($magentoProduct);
    }

    private function saveDirectly(ProductInterface $magentoProduct)
    {
        $this->assertImplementationOfProductInterface($magentoProduct);

        $magentoProduct->save();
    }
}
