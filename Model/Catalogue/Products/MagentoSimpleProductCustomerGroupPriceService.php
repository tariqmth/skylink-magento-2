<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\ProductTierPriceManagementInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductCustomerGroupPriceServiceInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Customers\CustomerGroupNotSyncedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PriceGroupPrice as SkyLinkPriceGroupPrice;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PricingStructure as SkyLinkPricingStructure;

class MagentoSimpleProductCustomerGroupPriceService implements MagentoSimpleProductCustomerGroupPriceServiceInterface
{
    private $magentoCustomerGroupRepository;

    private $magentoProductTierPriceManagement;

    public function __construct(
        MagentoCustomerGroupRepositoryInterface $magentoCustomerGroupRepository,
        ProductTierPriceManagementInterface $magentoProductTierPriceManagement
    ) {
        $this->magentoCustomerGroupRepository = $magentoCustomerGroupRepository;
        $this->magentoProductTierPriceManagement = $magentoProductTierPriceManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function syncCustomerGroupPrices(
        $magentoProductSku,
        SkyLinkPricingStructure $skyLinkPricingStructure
    ) {
        array_map(function (SkyLinkPriceGroupPrice $skyLinkPriceGroupPrice) use ($magentoProductSku) {
            /* @var \Magento\Customer\Api\Data\GroupInterface|null $magentoCustomerGroup */
            $magentoCustomerGroup = $this
                ->magentoCustomerGroupRepository
                ->findBySkyLinkPriceGroupKey($skyLinkPriceGroupPrice->getKey());

            if (null === $magentoCustomerGroup) {
                throw CustomerGroupNotSyncedException::withSkyLinkPriceGroupKey($skyLinkPriceGroupPrice->getKey());
            }

            // Add a tier price for the specific Customer Group with a minimum buy of 1
            $this->magentoProductTierPriceManagement->add(
                $magentoProductSku,
                $magentoCustomerGroup->getId(),
                $skyLinkPriceGroupPrice->getPrice()->toNative(),
                1
            );
        }, $skyLinkPricingStructure->getPriceGroupPrices());
    }
}
