<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoTierPriceMapperInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PriceGroupPrice as SkyLinkPriceGroupPrice;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;
use RetailExpress\SkyLink\Exceptions\Customers\CustomerGroupNotSyncedException;

class MagentoTierPriceMapper implements MagentoTierPriceMapperInterface
{
    private $magentoCustomerGroupRepository;

    private $storeManager;

    private $config;

    private $magentoCustomerGroups = [];

    private $websiteCurrencies;

    public function __construct(
        MagentoCustomerGroupRepositoryInterface $magentoCustomerGroupRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config
    ) {
        $this->magentoCustomerGroupRepository = $magentoCustomerGroupRepository;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function map(
        ProductInterface $magentoProduct,
        SkyLinkProduct $skyLinkProduct,
        WebsiteInterface $magentoWebsite = null
    ) {
        $magentoWebsiteId = isset($magentoWebsite) ? $magentoWebsite->getId() : 0;

        $tierPrices = $magentoProduct->getData('tier_price') ?: [];

        // We'll loop through all of the price group prices
        // @todo what about orphaned prices? Deleted stores? IDK
        array_map(function (SkyLinkPriceGroupPrice $skyLinkPriceGroupPrice) use ($magentoWebsiteId, &$tierPrices) {

            /* @var \Magento\Customer\Api\Data\GroupInterface $magentoCustomerGroup */
            $magentoCustomerGroup = $this->getMappedMagentoCustomerGroup($skyLinkPriceGroupPrice->getKey());

            // Let's filter down our tier prices by the given criteria, which allows us to preserve the key
            // so we can later merge existing tier prices back in
            $matching = array_filter(
                $tierPrices,
                function (array $tierPrice) use ($magentoWebsiteId, $magentoCustomerGroup) {
                    return $magentoWebsiteId == $tierPrice['website_id'] &&
                        $magentoCustomerGroup->getId() == $tierPrice['cust_group'] &&
                        1 == $tierPrice['price_qty'];
                }
            );

            // Grab a matching tier price or create one
            $tierPrice = count($matching) > 0 ? current($matching) : [
                'website_id' => $magentoWebsiteId,
                'cust_group' => $magentoCustomerGroup->getId(),
                'price_qty' => 1,
            ];

            // Update the price for the tier price
            $tierPrice['price'] = $tierPrice['website_price'] = $skyLinkPriceGroupPrice->getPrice()->toNative();
            $tierPrice['_touched'] = true; // Flag that we've touched the tier price

            if (count($matching) > 0) {
                $tierPrices[key($matching)] = $tierPrice;
            } else {
                $tierPrices[] = $tierPrice;
            }
        }, $skyLinkProduct->getPricingStructure()->getPriceGroupPrices());

        // Remove all tier prices for the website that weren't touched (they're orphaned)
        $tierPrices = array_filter($tierPrices, function (array $tierPrice) use ($magentoWebsiteId) {
            return $tierPrice['website_id'] != $magentoWebsiteId ||
                (array_key_exists('_touched', $tierPrice) && true === $tierPrice['_touched']);
        });

        // Remove the temporary touched variable
        $tierPrices = array_map(functiON (array $tierPrice) {
            if (array_key_exists('_touched', $tierPrice)) {
                unset($tierPrice['_touched']);
            }

            return $tierPrice;
        }, $tierPrices);

        if ($magentoWebsiteId != 0) {
            $tierPrices = $this->reduceTierPricesIfDuplicates($tierPrices, $magentoWebsiteId);
        }

        $magentoProduct->setData('tier_price', $tierPrices);
    }

    /**
     * Get the mapped Magento Customer Group for the given SkyLink Price Group Key.
     */
    private function getMappedMagentoCustomerGroup(SkyLinkPriceGroupKey $skyLinkPriceGroupKey)
    {
        $index = (string) $skyLinkPriceGroupKey;

        if (!array_key_exists($index, $this->magentoCustomerGroups)) {
            $this->magentoCustomerGroups[$index] = $this
                ->magentoCustomerGroupRepository
                ->findBySkyLinkPriceGroupKey($skyLinkPriceGroupKey);

            if (null === $this->magentoCustomerGroups[$index]) {
                throw CustomerGroupNotSyncedException::withSkyLinkPriceGroupKey($skyLinkPriceGroupKey);
            }
        }

        return $this->magentoCustomerGroups[$index];
    }

    /**
     * Magento's \Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice::validate() treats
     * prices for websites that have the same currency as the global configuration as duplicates. We'll compare
     * the given website's currency against the global currency and remove any "duplicate" prices.
     */
    private function reduceTierPricesIfDuplicates(array $tierPrices, $magentoWebsiteId)
    {
        if ($this->websiteDoesntUseBaseCurrency($magentoWebsiteId)) {
            return;
        }

        return array_filter(
            $tierPrices,
            function (array $websiteTierPrice) use ($magentoWebsiteId, $tierPrices) {

                // Don't interfere with tier prices not for this website
                if ($magentoWebsiteId != $websiteTierPrice['website_id']) {
                    return true;
                }

                // If the same price exists in the global website, let's remove it
                null === array_first($tierPrices, function ($key, array $globalTierPrice) use ($websiteTierPrice) {
                    return 0 == $globalTierPrice['website_id'] &&
                        $globalTierPrice['cust_group'] == $websiteTierPrice['cust_group'] &&
                        $globalTierPrice['price_qty'] == $websiteTierPrice['price_qty'] &&
                        $globalTierPrice['price'] == $websiteTierPrice['price'];
                });
            }
        );
    }

    /**
     * @return bool
     */
    private function websiteDoesntUseBaseCurrency($magentoWebsiteId)
    {
        return $this->getWebsiteCurrencies()[$magentoWebsiteId] != $this->getBaseCurrency();
    }

    /**
     * Gets the base currency.
     */
    private function getBaseCurrency()
    {
        return $this->config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE);
    }

    /**
     * Retrieve websites currencies.
     *
     * @return array
     */
    private function getWebsiteCurrencies()
    {
        if (null === $this->websiteCurrencies) {
            $this->websiteCurrencies = [];

            foreach ($this->storeManager->getWebsites() as $website) {
                $this->websiteCurrencies[$website->getId()] = $website->getBaseCurrencyCode();
            }

        }
        return $this->websiteCurrencies;
    }
}
