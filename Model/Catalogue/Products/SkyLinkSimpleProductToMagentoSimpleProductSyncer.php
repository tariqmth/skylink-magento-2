<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;

class SkyLinkSimpleProductToMagentoSimpleProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    const NAME = 'SkyLink Simple Product to Magento Simple Product';

    private $magentoSimpleProductRepository;

    private $magentoSimpleProductService;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    public function __construct(
        MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository,
        MagentoSimpleProductServiceInterface $magentoSimpleProductService,
        SkyLinkLoggerInterface $logger
    ) {
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;
        $this->magentoSimpleProductService = $magentoSimpleProductService;
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
    public function accepts(Product $skyLinkProduct)
    {
        return $skyLinkProduct instanceof SimpleProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function canSyncSkyLinkInventoryItemToMagentoStockItem()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(Product $skyLinkProduct)
    {
        /* @var \Magento\Catalog\Api\Data\ProductInterface|null $magentoProduct */
        $magentoProduct = $this->getMagentoProduct($skyLinkProduct);

        if (null !== $magentoProduct) {

            $this->logger->debug('Found Simple Product already mapped to the SkyLink Product, updating it.', [
                'SkyLink Product ID' => $skyLinkProduct->getId(),
                'SkyLink Product SKU' => $skyLinkProduct->getSku(),
                'Magento Product ID' => $magentoProduct->getId(),
                'Magento Product SKU' => $magentoProduct->getSku(),
            ]);

            $this->magentoSimpleProductService->updateMagentoProduct($magentoProduct, $skyLinkProduct);
        } else {
            $this->logger->debug('No Magento Simple Product exists for the SkyLink Product, creating one.', [
                'SkyLink Product ID' => $skyLinkProduct->getId(),
                'SkyLink Product SKU' => $skyLinkProduct->getSku(),
            ]);

            $magentoProduct = $this->magentoSimpleProductService->createMagentoProduct($skyLinkProduct);

            $this->logger->debug('Created a Magento Simple Product for the SkyLink Product.', [
                'SkyLink Product ID' => $skyLinkProduct->getId(),
                'SkyLink Product SKU' => $skyLinkProduct->getSku(),
                'Magento Product ID' => $magentoProduct->getId(),
                'Magento Product SKU' => $magentoProduct->getSku(),
            ]);
        }

        return $magentoProduct;
    }

    public function syncSkyLinkInventoryItemToMagentoStockItem(Product $skyLinkProduct)
    {
        /* @var \Magento\Catalog\Api\Data\ProductInterface|null $magentoProduct */
        $magentoProduct = $this->getMagentoProduct($skyLinkProduct);

        if (null === $magentoProduct) {
            $skyLinkProductId = $skyLinkProduct->getId();

            $e = ExistingMagentoProductRequiredToSyncSkyLinkInventoryItemToStockItemException::withSkyLinkProductId(
                $skyLinkProductId
            );

            $this->logger->error($e->getMessage(), [
                'SkyLink Product ID' => $skyLinkProductId,
                'SkyLink Product SKU' => $skyLinkProduct->getSku(),
            ]);

            throw $e;
        }

        $this->magentoSimpleProductService->updateMagentoProductStockItem($magentoProduct, $skyLinkProduct);

        $skyLinkInventoryItem = $skyLinkProduct->getInventoryItem();
        $this->logger->debug('Updated the Magento Product\'s stock.', [
            'SkyLink Product ID' => $skyLinkProduct->getId(),
            'SkyLink Product SKU' => $skyLinkProduct->getSku(),
            'Magento Product ID' => $magentoProduct->getId(),
            'Magento Product SKU' => $magentoProduct->getSku(),
            'Stock Is Managed' => $skyLinkInventoryItem->isManaged(),
            'Qty In Stock' => $skyLinkInventoryItem->getQty(),
        ]);
    }

    private function getMagentoProduct(Product $skyLinkProduct)
    {
        try {
            return $this->magentoSimpleProductRepository->findBySkyLinkProductId($skyLinkProduct->getId());
        } catch (TooManyProductMatchesException $e) {

            $this->logger->error($e->getMessage(), [
                'SkyLink Product ID' => $skyLinkProduct->getId(),
                'SkyLink Product SKU' => $skyLinkProduct->getSku(),
            ]);

            throw $e;
        }
    }
}
