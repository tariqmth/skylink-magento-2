<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductServiceInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;

class SkyLinkMatrixToMagentoConfigurableProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    const NAME = 'SkyLink Product Matrix to Magento Configurable Product';

    private $magentoConfigurableProductRepository;

    private $magentoConfigurableProductService;

    private $magentoConfigurableProductLinkManagement;

    private $simpleProductSyncer;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    public function __construct(
        MagentoConfigurableProductRepositoryInterface $magentoConfigurableProductRepository,
        MagentoConfigurableProductServiceInterface $magentoConfigurableProductService,
        MagentoConfigurableProductLinkManagementInterface $magentoConfigurableProductLinkManagement,
        SkyLinkSimpleProductToMagentoSimpleProductSyncer $simpleProductSyncer,
        SkyLinkLoggerInterface $logger
    ) {
        $this->magentoConfigurableProductRepository = $magentoConfigurableProductRepository;
        $this->magentoConfigurableProductService = $magentoConfigurableProductService;
        $this->magentoConfigurableProductLinkManagement = $magentoConfigurableProductLinkManagement;
        $this->simpleProductSyncer = $simpleProductSyncer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts(Product $skyLinkMatrix)
    {
        return $skyLinkMatrix instanceof Matrix;
    }

    public function canSyncSkyLinkInventoryItemToMagentoStockItem()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(Product $skyLinkMatrix)
    {
        $this->logger->debug('Syncing all SkyLink Simple Products contained in the SkyLink Product Matrix.', [
            'SkyLink Product Matrix SKU' => $skyLinkMatrix->getSku(),
        ]);

        // We'll sync the simple products in the Matrix
        $magentoSimpleProducts = array_map(function (SimpleProduct $skyLinkProduct) {
            return $this->simpleProductSyncer->sync($skyLinkProduct); // @todo prioritise the URL key for the configurable product created later
        }, $skyLinkMatrix->getProducts());

        // Grab our SkyLink product IDs
        $skyLinkProductIds = array_map(function (SimpleProduct $skyLinkProduct) {
            return $skyLinkProduct->getId();
        }, $skyLinkMatrix->getProducts());

        // Attempt to find an existing product using those IDs
        $magentoConfigurableProduct = $this
            ->magentoConfigurableProductRepository
            ->findBySkyLinkProductIds($skyLinkProductIds);

        if (null !== $magentoConfigurableProduct) {
            $this->logger->debug('Found existing Magento Configurable Product appropriate for SkyLink Simple Products in the SkyLink Product Matrix, updating it.', [
                'SkyLink Product Matrix SKU' => $skyLinkMatrix->getSku(),
                'SkyLink Simple Product IDs' => array_map(function (SkyLinkProductId $skyLinkProductId) {
                    return (string) $skyLinkProductId;
                }, $skyLinkProductIds),
                'Magento Configurable Product ID' => $magentoConfigurableProduct->getId(),
                'Magento Configurable Product SKU' => $magentoConfigurableProduct->getSku(),
            ]);

            $this
                ->magentoConfigurableProductService
                ->updateMagentoProduct($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
        } else {

            $this->logger->debug('Couldn\'t find existing Magento Configurable Product appropriate for SkyLink Simple Products in the SkyLink Product Matrix, creating one.', [
                'SkyLink Product Matrix SKU' => $skyLinkMatrix->getSku(),
                'SkyLink Simple Product IDs' => array_map(function (SkyLinkProductId $skyLinkProductId) {
                    return (string) $skyLinkProductId;
                }, $skyLinkProductIds),
            ]);

            $magentoConfigurableProduct = $this
                ->magentoConfigurableProductService
                ->createMagentoProduct($skyLinkMatrix, $magentoSimpleProducts);

            $this->logger->debug('Created a Magento Configurable Product appropriate for the SkyLink Product Matrix.', [
                'SkyLink Product Matrix SKU' => $skyLinkMatrix->getSku(),
                'Magento Configurable Product ID' => $magentoConfigurableProduct->getId(),
                'Magento Configurable Product SKU' => $magentoConfigurableProduct->getSku(),
            ]);
        }

        return $magentoConfigurableProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function syncSkyLinkInventoryItemToMagentoStockItem(Product $skyLinkProduct)
    {
        // We don't support
    }
}
