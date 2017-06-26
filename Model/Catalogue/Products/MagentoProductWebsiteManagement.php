<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product\WebsiteFactory as MagentoProductWebsiteFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
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

    private $magentoProductWebsiteLinkRepository;

    private $magentoProductWebsiteLinkFactory;

    public function __construct(
        MagentoStoreEmulatorInterface $magentoStoreEmulator,
        MagentoProductMapperInterface $magentoProductMapper,
        ProductRepositoryInterface $magentoProductRepository,
        MagentoWebsiteRepositoryInterface $magentoWebsiteRepository,
        ProductWebsiteLinkRepositoryInterface $magentoProductWebsiteLinkRepository,
        ProductWebsiteLinkInterfaceFactory $magentoProductWebsiteLinkFactory
    ) {
        $this->magentoStoreEmulator = $magentoStoreEmulator;
        $this->magentoProductMapper = $magentoProductMapper;
        $this->magentoProductRepository = $magentoProductRepository;
        $this->magentoWebsiteRepository = $magentoWebsiteRepository;
        $this->magentoProductWebsiteLinkRepository = $magentoProductWebsiteLinkRepository;
        $this->magentoProductWebsiteLinkFactory = $magentoProductWebsiteLinkFactory;
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
            $this->magentoStoreEmulator->onWebsite(
                $magentoWebsite,
                function (StoreInterface $magentoStore, WebsiteInterface $magentoWebsite) use ($magentoProduct, $skyLinkProduct) {

                    // Load the product in the context of the website's store
                    $magentoProduct = $this->magentoProductRepository->getById(
                        $magentoProduct->getId(),
                        false,
                        $magentoStore->getId()
                    );

                    // Map and save the product
                    $this->magentoProductMapper->mapMagentoProductForWebsite(
                        $magentoProduct,
                        $skyLinkProduct,
                        $magentoWebsite
                    );

                    $this->magentoProductRepository->save($magentoProduct);
                }
            );

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
        array_walk($removeFromWebsiteIds, function ($websiteId) use ($magentoProduct) {
            $this->magentoProductWebsiteLinkRepository->deleteById($magentoProduct->getSku(), $websiteId);
        });

        /* @var int[] $addToWebsiteIds */
        $addToWebsiteIds = array_diff($allowedWebsiteIds, $currentlyAssignedWebsiteIds);
        array_walk($addToWebsiteIds, function ($websiteId) use ($magentoProduct) {
            /* @var \Magento\Catalog\Api\Data\ProductWebsiteLinkInterface $magentoProductWebsiteLink */
            $magentoProductWebsiteLink = $this->magentoProductWebsiteLinkFactory->create();
            $magentoProductWebsiteLink
                ->setSku($magentoProduct->getSku())
                ->setWebsiteId($websiteId);

            $this->magentoProductWebsiteLinkRepository->save($magentoProductWebsiteLink);
        });
    }
}
