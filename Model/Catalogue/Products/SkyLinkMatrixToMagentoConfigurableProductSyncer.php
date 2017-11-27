<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\ProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductWebsiteManagementInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Exceptions\Products\ProductAlreadyExistsAsTheWrongTypeException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelGroupRepositoryInterface;

class SkyLinkMatrixToMagentoConfigurableProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    use SkyLinkProductToMagentoProductSyncer;

    const NAME = 'SkyLink Product Matrix to Magento Configurable Product';

    private $magentoConfigurableProductRepository;

    private $magentoConfigurableProductService;

    private $magentoConfigurableProductLinkManagement;

    private $magentoProductWebsiteManagement;

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
        ProductRepositoryInterface $baseMagentoProductRepository,
        SkyLinkSimpleProductToMagentoSimpleProductSyncer $simpleProductSyncer,
        MagentoProductWebsiteManagementInterface $magentoProductWebsiteManagement,
        SkyLinkLoggerInterface $logger,
        SalesChannelGroupRepositoryInterface $salesChannelGroupRepository
    ) {
        $this->magentoConfigurableProductRepository = $magentoConfigurableProductRepository;
        $this->magentoConfigurableProductService = $magentoConfigurableProductService;
        $this->magentoConfigurableProductLinkManagement = $magentoConfigurableProductLinkManagement;
        $this->magentoProductWebsiteManagement = $magentoProductWebsiteManagement;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->simpleProductSyncer = $simpleProductSyncer;
        $this->logger = $logger;
        $this->salesChannelGroupRepository = $salesChannelGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts(SkyLinkProduct $skyLinkMatrix)
    {
        return $skyLinkMatrix instanceof Matrix;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(SkyLinkProduct $skyLinkMatrix, array $skyLinkProductInSalesChannelGroups)
    {
        $this->logger->debug('Syncing all SkyLink Simple Products contained in the SkyLink Product Matrix.', [
            'SkyLink Product Matrix SKU' => $skyLinkMatrix->getSku(),
        ]);

        // We'll sync the simple products in the Matrix
        $magentoSimpleProducts = array_map(function (SimpleProduct $skyLinkProduct) use ($skyLinkProductInSalesChannelGroups) {
            return $this->simpleProductSyncer->sync($skyLinkProduct, $skyLinkProductInSalesChannelGroups);
        }, $skyLinkMatrix->getProducts());

        // Grab our SkyLink product IDs
        $skyLinkProductIds = array_map(function (SimpleProduct $skyLinkProduct) {
            return $skyLinkProduct->getId();
        }, $skyLinkMatrix->getProducts());

        // Attempt to find an existing product using it's manufacturer SKU
        $magentoConfigurableProduct = $this
            ->magentoConfigurableProductRepository
            ->findBySkyLinkManufacturerSku($skyLinkMatrix->getManufacturerSku());

        if (null !== $magentoConfigurableProduct) {
            $this->logger->debug('Found existing Magento Configurable Product appropriate for SkyLink Simple Products in the SkyLink Product Matrix, updating it.', [
                'SkyLink Product Matrix SKU' => $skyLinkMatrix->getSku(),
                'SkyLink Simple Product IDs' => array_map(function (SkyLinkProductId $skyLinkProductId) {
                    return (string) $skyLinkProductId;
                }, $skyLinkProductIds),
                'Magento Configurable Product ID' => $magentoConfigurableProduct->getId(),
                'Magento Configurable Product SKU' => $magentoConfigurableProduct->getSku(),
            ]);

            $magentoConfigurableProduct = $this
                ->magentoConfigurableProductService
                ->updateMagentoProduct($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
        } elseif ($existingMagentoProduct = $this->getMagentoProduct((string) $skyLinkMatrix->getSku())) {
            throw ProductAlreadyExistsAsTheWrongTypeException::withMatrix($skyLinkMatrix, $existingMagentoProduct);
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

        $this->magentoProductWebsiteManagement->assignMagentoProductToWebsitesForSalesChannelGroups(
            $magentoConfigurableProduct,
            $skyLinkProductInSalesChannelGroups
        );

        return $magentoConfigurableProduct;
    }
}
