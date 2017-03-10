<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\WebsiteFactory as MagentoProductWebsiteFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductCustomerGroupPriceServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductWebsiteManagementInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Segregation\MagentoStoreEmulatorInterface;
use RetailExpress\SkyLink\Api\Segregation\MagentoWebsiteRepositoryInterface;

class MagentoProductWebsiteManagement implements MagentoProductWebsiteManagementInterface
{
    use ProductInterfaceAsserter;

    private $magentoStoreEmulator;

    private $magentoProductMapper;

    private $magentoProductRepository;

    private $magentoCustomerGroupPriceService;

    private $magentoProductWebsiteFactory;

    public function __construct(
        MagentoStoreEmulatorInterface $magentoStoreEmulator,
        MagentoProductMapperInterface $magentoProductMapper,
        ProductRepositoryInterface $magentoProductRepository,
        MagentoProductCustomerGroupPriceServiceInterface $magentoCustomerGroupPriceService,
        MagentoProductWebsiteFactory $magentoProductWebsiteFactory,
        MagentoWebsiteRepositoryInterface $magentoWebsiteRepository
    ) {
        $this->magentoStoreEmulator = $magentoStoreEmulator;
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoProductRepository = $magentoProductRepository;
        $this->magentoCustomerGroupPriceService = $magentoCustomerGroupPriceService;
        $this->magentoProductWebsiteFactory = $magentoProductWebsiteFactory;
        $this->magentoWebsiteRepository = $magentoWebsiteRepository;
    }

    /**
     * Overrides the given Magento Product within the context of a SkyLink Product in a Sales Channel Group.
     *
     * @param ProductInterface                           $magentoProduct
     * @param SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
     */
    public function overrideMagentoProductForSalesChannelGroup(
        ProductInterface $magentoProduct,
        SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup
    ) {
        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\Product $skyLinkProduct */
        $skyLinkProduct = $skyLinkProductInSalesChannelGroup->getSkyLinkProduct();

        /* @var WebsiteInterface[] $magentoWebsites */
        $magentoWebsites = $skyLinkProductInSalesChannelGroup->getSalesChannelGroup()->getMagentoWebsites();

        // Loop through all the Magento Websites that the Sales Channel Group uses and scope into each
        array_map(function (WebsiteInterface $magentoWebsite) use ($magentoProduct, $skyLinkProduct) {

            // Map the product in the context of the given Magento Website
            $this->magentoStoreEmulator->onWebsite($magentoWebsite, function () use ($magentoProduct, $skyLinkProduct) {
                $this->magentoProductMapper->mapMagentoProductForWebsite($magentoProduct, $skyLinkProduct);
            });

            // Save the product
            $this->magentoProductRepository->save($magentoProduct);

            // And sync Customer Group Prices for the given Magento Website
            $this->magentoStoreEmulator->onWebsite($magentoWebsite, function () use ($magentoProduct, $skyLinkProduct) {
                $this->magentoCustomerGroupPriceService->syncCustomerGroupPrices(
                    $magentoProduct,
                    $skyLinkProduct->getPricingStructure()
                );
            });
        }, $magentoWebsites);
    }

    /**
     * Assigns the given product to the given Magento Websites based on the array of SkyLink Product in Sales Channel Groups
     *
     * @param ProductInterface                                                                                $magentoProduct
     * @param \[] $skyLinkProductInSalesChannelGroups
     */
    public function assignMagentoProductToWebsitesForSalesChannelGroups(
        ProductInterface $magentoProduct,
        array $skyLinkProductInSalesChannelGroups
    ) {
        $this->assertImplementationOfProductInterface($magentoProduct);

        /* @var RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface[] $salesChannelGroups */
        $salesChannelGroups = array_map(
            function (SkyLinkProductInSalesChannelGroupInterface $skyLinkProductInSalesChannelGroup) {
                return $skyLinkProductInSalesChannelGroup->getSalesChannelGroup();
            },
            $skyLinkProductInSalesChannelGroups
        );

        /* @var WebsiteInterface[] $allowedWebsites */
        $allowedWebsites = $this
            ->magentoWebsiteRepository
            ->getListFilteredByGlobalSalesChannelIdAndSalesChannelGroups($salesChannelGroups);

        /* @var int[] $allowedWebsiteIds */
        $allowedWebsiteIds = array_map(function (WebsiteInterface $allowedWebsite) {
            return (int) $allowedWebsite->getId();
        }, $allowedWebsites);

        /* @var int[] $currentlyAssignedWebsiteIds */
        $currentlyAssignedWebsiteIds = array_map('intval', $magentoProduct->getWebsiteIds());

        /* @var int[] $removeFromWebsiteIds */
        $removeFromWebsiteIds = array_diff($currentlyAssignedWebsiteIds, $allowedWebsiteIds);

        /* @var \Magento\Catalog\Model\Product\Website $magentoProductWebsite */
        $magentoProductWebsite = $this->magentoProductWebsiteFactory->create();
        $magentoProductWebsite->removeProducts($removeFromWebsiteIds, [$magentoProduct->getId()]);
    }
}
