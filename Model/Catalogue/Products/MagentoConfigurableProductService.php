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
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;

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

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    public function __construct(
        MagentoProductMapperInterface $magentoProductMapper,
        MagentoConfigurableProductStockItemUpdaterInterface $magentoStockItemUpdater,
        ProductInterfaceFactory $magentoProductFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        UrlKeyGeneratorInterface $urlKeyGenerator,
        MagentoConfigurableProductLinkManagementInterface $magentoConfigurableProductLinkManagement,
        StockItemInterfaceFactory $magentoStockItemFactory,
        StockRegistryInterface $magentoStockRegistry,
        SkyLinkLoggerInterface $logger
    ) {
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoStockItemUpdater = $magentoStockItemUpdater;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->magentoConfigurableProductLinkManagement = $magentoConfigurableProductLinkManagement;
        $this->magentoStockItemFactory = $magentoStockItemFactory;
        $this->magentoStockRegistry = $magentoStockRegistry;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoProduct(SkyLinkMatrix $skyLinkMatrix, array $magentoSimpleProducts)
    {
        /* @var ProductInterface $magentoConfigurableProduct */
        $magentoConfigurableProduct = $this->magentoProductFactory->create();
        $magentoConfigurableProduct->setTypeId(ConfigurableProductType::TYPE_CODE);

        $magentoConfigurableProduct->setSku((string) $skyLinkMatrix->getSku());

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

        $this->updateSkuIfNeeded($magentoConfigurableProduct, $skyLinkMatrix);
        $this->mapProduct($magentoConfigurableProduct, $skyLinkMatrix);
        $this->mapMagentoAttributes($magentoConfigurableProduct);
        $this->linkSimpleProducts($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
        $this->updateStockAndSave($magentoConfigurableProduct, $magentoStockItem);

        return $magentoConfigurableProduct;
    }

    /**
     * Updates the SKU on the given product and refreshes it (if needed).
     */
    private function updateSkuIfNeeded(ProductInterface &$magentoProduct, SkyLinkMatrix $skyLinkProduct)
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
        $magentoProduct->save(); // You can't save through the repository because tiered prices crap out
        $magentoProduct = $this->baseMagentoProductRepository->getById($magentoProduct->getId(), true, null, true);
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

    private function updateStockAndSave(
        ProductInterface &$magentoConfigurableProduct,
        StockItemInterface $magentoStockItem
    ) {
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
