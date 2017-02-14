<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class SkyLinkSimpleProductToMagentoSimpleProductSyncer implements SkyLinkProductToMagentoProductSyncerInterface
{
    use SkyLinkProductToMagentoProductSyncer;

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
    public function accepts(SkyLinkProduct $skyLinkProduct)
    {
        return $skyLinkProduct instanceof SimpleProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(SkyLinkProduct $skyLinkProduct, array $skyLinkProductInSalesChannelGroups)
    {
        try {
            /* @var \Magento\Catalog\Api\Data\ProductInterface $magentoProduct */
            $magentoProduct = $this->magentoSimpleProductRepository->findBySkyLinkProductId($skyLinkProduct->getId());
        } catch (TooManyProductMatchesException $e) {
            $this->logger->error($e->getMessage(), [
                'SkyLink Product ID' => $skyLinkProduct->getId(),
                'SkyLink Product SKU' => $skyLinkProduct->getSku(),
            ]);

            throw $e;
        }

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

        // If there were no variations in different sales channel groups, we can end now
        if (count($skyLinkProductInSalesChannelGroups) < 1) {
            return $magentoProduct;
        }

        $this->logger->debug('Updating Magento Product data using SkyLink Product data fetched from all configured Sales Channel IDs.', [
            'SkyLink Product ID' => $skyLinkProduct->getId(),
            'SkyLink Product SKU' => $skyLinkProduct->getSku(),
            'Magento Product ID' => $magentoProduct->getId(),
            'Magento Product SKU' => $magentoProduct->getSku(),
            'Sales Channel IDs' => array_map(
                function (SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup) {
                    return (string) $skyLinkProductInSalesChannelGroup
                        ->getSalesChannelGroup()
                        ->getSalesChannelId();
                },
                $skyLinkProductInSalesChannelGroups
            ),
        ]);

        // Loop through the product in all Sales Channel Groups and update values
        array_walk(
            $skyLinkProductInSalesChannelGroups,
            function (SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup) use ($magentoProduct) {
                $this->magentoSimpleProductService->updateMagentoProductForSalesChannelGroup(
                    $magentoProduct,
                    $skyLinkProductInSalesChannelGroup
                );
            }
        );

        return $magentoProduct;
    }
}
