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
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductStockItemMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Api\Catalogue\Products\UrlKeyGeneratorInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Segregation\MagentoWebsiteRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;

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

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

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
        SkyLinkLoggerInterface $logger
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
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoProduct(SkyLinkProduct $skyLinkProduct)
    {
        /* @var ProductInterface $magentoProduct */
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setTypeId(ProductType::TYPE_SIMPLE);

        $magentoProduct->setSku((string) $skyLinkProduct->getSku());

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

        $this->updateSkuIfNeeded($magentoProduct, $skyLinkProduct);
        $this->mapProduct($magentoProduct, $skyLinkProduct);
        $this->mapMagentoAttributes($magentoProduct);
        $this->mapStockAndSave($magentoProduct, $magentoStockItem, $skyLinkProduct);

        return $magentoProduct;
    }

    /**
     * Updates the SKU on the given product and refreshes it (if needed).
     */
    private function updateSkuIfNeeded(ProductInterface &$magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $existingSku = $magentoProduct->getSku();

        // If the SKU changes, we'll quickly save the product and continue
        if ($existingSku === (string) $skyLinkProduct->getSku()) {
            return;
        }

        $this->logger->debug(
            'The SKU appears to have changed, updating Magento and starting mapping.',
            [
                'Existing SKU in Magento' => $existingSku,
                'New SKU in SkyLink' => $skyLinkProduct->getSku(),
            ]
        );

        $magentoProduct->setSku((string) $skyLinkProduct->getSku());
        $magentoProduct->save(); // You can't save through the repository becuase tiered prices crap out
        $magentoProduct = $this->baseMagentoProductRepository->getById($magentoProduct->getId(), true, null, true);
    }

    private function mapProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $this->magentoProductMapper->mapMagentoProduct($magentoProduct, $skyLinkProduct);
    }

    private function setUrlKeyForMappedProduct(ProductInterface $magentoProduct)
    {
        $urlKey = $this->urlKeyGenerator->generateUniqueUrlKeyForMagentoProduct($magentoProduct);
        $magentoProduct->unsetData('url_key');
        $magentoProduct->setCustomAttribute('url_key', $urlKey);
    }

    /**
     * Map across the previous attributes of the Magento product to the newly created Magento product.
     * Applies to product images.
     *
     * @param ProductInterface &$newProduct
     * @todo Add other attributes unrelated to Skylink
     */
    private function mapMagentoAttributes(ProductInterface $newProduct)
    {
        try {
            $originalProduct = $this->baseMagentoProductRepository->get($newProduct->getSku());
            $newProduct->setMediaGalleryEntries($originalProduct->getMediaGalleryEntries());
        } catch (NoSuchEntityException $e) {
            $this->logger->debug('We tried to copy attribute data from the existing product in Magento,
                but the product could not be found.',
                ['Product SKU' => $newProduct->getSku()]
            );
        }
    }

    private function mapStockAndSave(
        ProductInterface &$magentoProduct,
        StockItemInterface $magentoStockItem,
        SkyLinkProduct $skyLinkProduct
    ) {
        $this->magentoStockItemMapper->mapStockItem($magentoStockItem, $skyLinkProduct->getInventoryItem());
        $magentoProduct = $this->baseMagentoProductRepository->save($magentoProduct);
        $this->magentoStockRegistry->updateStockItemBySku($magentoProduct->getSku(), $magentoStockItem);
    }
}
