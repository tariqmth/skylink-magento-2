<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ProductTierPriceManagementInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductCustomerGroupPriceServiceInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Customers\CustomerGroupNotSyncedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PriceGroupPrice as SkyLinkPriceGroupPrice;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PricingStructure as SkyLinkPricingStructure;

class MagentoProductCustomerGroupPriceService implements MagentoProductCustomerGroupPriceServiceInterface
{
    const CUSTOMER_GROUP_PRICE_QTY = 1;

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
        ProductInterface $magentoProduct,
        SkyLinkPricingStructure $skyLinkPricingStructure
    ) {
        array_map(function (SkyLinkPriceGroupPrice $skyLinkPriceGroupPrice) use ($magentoProduct) {
            /* @var \Magento\Customer\Api\Data\GroupInterface|null $magentoCustomerGroup */
            $magentoCustomerGroup = $this
                ->magentoCustomerGroupRepository
                ->findBySkyLinkPriceGroupKey($skyLinkPriceGroupPrice->getKey());

            if (null === $magentoCustomerGroup) {
                throw CustomerGroupNotSyncedException::withSkyLinkPriceGroupKey($skyLinkPriceGroupPrice->getKey());
            }

            $magentoProductSku = $magentoProduct->getSku();
            $magentoCustomerGroupId = $magentoCustomerGroup->getId();
            $tierPriceValue = $skyLinkPriceGroupPrice->getPrice()->toNative();

            /* @var ProductTierPriceInterface|null $existingTierPrice */
            $existingTierPrice = $this->findExistingTierPrice($magentoProductSku, $magentoCustomerGroupId);

            // If there is an existing tier price, we'll compare it against ours and remove it if need-be
            if (null !== $existingTierPrice) {

                // If our new value is the same as the existing, we'll just return
                if ($tierPriceValue == $existingTierPrice->getValue()) {
                    return;
                }

                // If they're different, we'll need to delete the existing tier price
                $this->magentoProductTierPriceManagement->remove(
                    $magentoProductSku,
                    $magentoCustomerGroupId,
                    self::CUSTOMER_GROUP_PRICE_QTY
                );
            }

            // At this point, there is either no existing tier price, or there was, and we removed it
            $this->magentoProductTierPriceManagement->add(
                $magentoProductSku,
                $magentoCustomerGroupId,
                $tierPriceValue,
                self::CUSTOMER_GROUP_PRICE_QTY
            );
        }, $skyLinkPricingStructure->getPriceGroupPrices());
    }

    private function findExistingTierPrice($magentoProductSku, $magentoCustomerGroupId)
    {
        /* @var ProductTierPriceInterface[] $tierPrices */
        $tierPrices = $this->magentoProductTierPriceManagement->getList($magentoProductSku, $magentoCustomerGroupId);

        // Find our tier price by the quantity
        return array_first($tierPrices, function ($key, ProductTierPriceInterface $tierPrice) {
            return $tierPrice->getQty() == self::CUSTOMER_GROUP_PRICE_QTY; // Non-strict comparison
        });
    }
}
