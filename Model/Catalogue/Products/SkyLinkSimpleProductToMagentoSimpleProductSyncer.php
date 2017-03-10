<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductWebsiteManagementInterface;
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

    private $magentoProductWebsiteManagement;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    public function __construct(
        MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository,
        MagentoSimpleProductServiceInterface $magentoSimpleProductService,
        MagentoProductWebsiteManagementInterface $magentoProductWebsiteManagement,
        SkyLinkLoggerInterface $logger
    ) {
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;
        $this->magentoSimpleProductService = $magentoSimpleProductService;
        $this->magentoProductWebsiteManagement = $magentoProductWebsiteManagement;
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
            /* @var ProductInterface $magentoProduct */
            $magentoProduct = $this->magentoSimpleProductRepository->findBySkyLinkProductId($skyLinkProduct->getId());
        } catch (TooManyProductMatchesException $e) {
            $this->log('error', $e->getMessage(), $skyLinkProduct);
            throw $e;
        }

        if (null !== $magentoProduct) {

            $this->logDebug(
                'Found Simple Product already mapped to the SkyLink Product, updating it.',
                $skyLinkProduct,
                $magentoProduct
            );

            $magentoProduct = $this->magentoSimpleProductService->updateMagentoProduct($magentoProduct, $skyLinkProduct);
        } else {
            $this->logDebug(
                'No Magento Simple Product exists for the SkyLink Product, creating one.',
                $skyLinkProduct
            );

            $magentoProduct = $this->magentoSimpleProductService->createMagentoProduct($skyLinkProduct);

            $this->logDebug(
                'Created a Magento Simple Product for the SkyLink Product.',
                $skyLinkProduct,
                $magentoProduct
            );
        }

        // If there were no variations in different sales channel groups, we can end now
        if (count($skyLinkProductInSalesChannelGroups) > 0) {
            $this->logDebug(
                'Overriding Magento Product data using SkyLink Product data fetched from all configured Sales Channel IDs.',
                $skyLinkProduct,
                $magentoProduct,
                [
                    'Sales Channel IDs' => array_map(
                        function (SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup) {
                            return (string) $skyLinkProductInSalesChannelGroup
                                ->getSalesChannelGroup()
                                ->getSalesChannelId();
                        },
                        $skyLinkProductInSalesChannelGroups
                    )
                ]
            );

            // Loop through the product in all Sales Channel Groups and update values
            array_walk(
                $skyLinkProductInSalesChannelGroups,
                function (SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup) use ($magentoProduct) {
                    $this->magentoProductWebsiteManagement->overrideMagentoProductForSalesChannelGroup(
                        $magentoProduct,
                        $skyLinkProductInSalesChannelGroup
                    );
                }
            );
        }

        // @todo WTF is this? Totally needs to be abstracted away, it's clogging this class up (as with all the other logging)...
        $this->logDebug(
            'Assigning Magento Product to Websites.',
            $skyLinkProduct,
            $magentoProduct,
            [
                'Sales Channel Groups' => array_map(
                    function (SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup) {

                        $salesChannelGroup = $skyLinkProductInSalesChannelGroup->getSalesChannelGroup();

                        return [
                            'Sales Channel ID' => $salesChannelGroup->getSalesChannelId(),
                            'Websites' => array_map(function (WebsiteInterface $magentoWebsite) {
                                return [
                                    'ID' => $magentoWebsite->getId(),
                                    'Name' => $magentoWebsite->getName(),
                                ];
                            }, $salesChannelGroup->getMagentoWebsites()),
                        ];
                    },
                    $skyLinkProductInSalesChannelGroups
                ),
            ]
        );

        $this->magentoProductWebsiteManagement->assignMagentoProductToWebsitesForSalesChannelGroups(
            $magentoProduct,
            $skyLinkProductInSalesChannelGroups
        );

        return $magentoProduct;
    }

    private function logDebug($message, SkyLinkProduct $skyLinkProduct, ProductInterface $magentoProduct = null, array $additional = null)
    {
        $this->log('debug', $message, $skyLinkProduct, $magentoProduct, $additional);
    }

    private function log($level, $message, SkyLinkProduct $skyLinkProduct, ProductInterface $magentoProduct = null, array $additional = null)
    {
        $data = [
            'SkyLink Product ID' => $skyLinkProduct->getId(),
            'SkyLink Product SKU' => $skyLinkProduct->getSku(),
        ];

        if (null !== $magentoProduct) {
            $data['Magento Product ID'] = $magentoProduct->getId();
            $data['Magento Product SKU'] = $magentoProduct->getSku();
        }

        if (null !== $additional) {
            $data = array_merge($data, $additional);
        }

        $this->logger->$level($message, $data);
    }
}
