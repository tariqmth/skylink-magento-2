<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use DateTimeImmutable;
use DateTimeZone;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeTypeManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Exceptions\Customers\CustomerGroupNotSyncedException;
use RetailExpress\SkyLink\Exceptions\Products\AttributeNotMappedException;
use RetailExpress\SkyLink\Exceptions\Products\AttributeOptionNotMappedException;
use RetailExpress\SkyLink\Model\Catalogue\SyncStrategy;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\PriceGroupPrice as SkyLinkPriceGroupPrice;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoProductMapper implements MagentoProductMapperInterface
{
    use ProductInterfaceAsserter;

    private $productConfig;

    private $attributeSetRepository;

    private $attributeRepository;

    private $attributeTypeManager;

    private $attributeOptionRepository;

    private $magentoCustomerGroupRepository;

    private $dateTime;

    private $timezone;

    public function __construct(
        ConfigInterface $productConfig,
        MagentoAttributeSetRepositoryInterface $attributeSetRepository,
        MagentoAttributeRepositoryInterface $attributeRepository,
        MagentoAttributeTypeManagerInterface $attributeTypeManager,
        MagentoAttributeOptionRepositoryInterface $attributeOptionRepository,
        MagentoCustomerGroupRepositoryInterface $magentoCustomerGroupRepository,
        DateTime $dateTime,
        TimezoneInterface $timezone
    ) {
        $this->productConfig = $productConfig;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeTypeManager = $attributeTypeManager;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->magentoCustomerGroupRepository = $magentoCustomerGroupRepository;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function mapMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        // New products require a little more mapping
        if (!$magentoProduct->getId()) {
            $this->mapNewMagentoProduct($magentoProduct, $skyLinkProduct);
        } else {
            $this->overrideVisibilityForExistingProduct($magentoProduct);
        }

        $magentoProduct->setSku((string) $skyLinkProduct->getSku());

        $magentoProduct->unsetData('manufacturer_sku');
        $magentoProduct->setData('manufacturer_sku', (string) $skyLinkProduct->getManufacturerSku());

        $this->mapName($magentoProduct, $skyLinkProduct);
        $this->mapPrices($magentoProduct, $skyLinkProduct);
        $this->mapCustomerGroupPrices($magentoProduct, $skyLinkProduct);
        $this->mapQuantities($magentoProduct, $skyLinkProduct);

        // Use the cubic weight for the given product
        // @todo this should be configuration-based
        $magentoProduct->setWeight($skyLinkProduct->getPhysicalPackage()->getWeight()->toNative());

        // All other attributes
        $this->mapAttributes($magentoProduct, $skyLinkProduct);
    }

    public function mapMagentoProductForWebsite(
        ProductInterface $magentoProduct,
        SkyLinkProduct $skyLinkProduct,
        WebsiteInterface $magentoWebsite
    ) {
        $this->mapName($magentoProduct, $skyLinkProduct);
        $this->mapPrices($magentoProduct, $skyLinkProduct);
        $this->mapCustomerGroupPrices($magentoProduct, $skyLinkProduct, $magentoWebsite);
    }

    /**
     * Maps a new Magento Product based on the given SkyLink Product.
     *
     * @param ProductInterface $product
     * @param SkyLinkProduct   $skyLinkProduct
     */
    private function mapNewMagentoProduct(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        // Set the attribute set according to what is mapped for the given product type
        $magentoProduct->setAttributeSetId(
            $this
                ->attributeSetRepository
                ->getAttributeSetForProductType($skyLinkProduct->getProductType())->getId()
        );

        $magentoProduct->unsetData('skylink_product_id');
        $magentoProduct->setCustomAttribute('skylink_product_id', (string) $skyLinkProduct->getId());
    }

    /**
     * Looks at an existing Magento Product's visibility and if it's been marked as invisible,
     * will make it as visible in both catalogue and search. Product who have been adjsuted
     * to be visible in only catalogue or search will not be modified. This is to account
     * for products that were in a configurable product that no longer are, they must
     * become visible.
     *
     * @todo Because this is called during the configurable product syncing process, all child
     *       products will be set to visible, and then back in MagentoConfigurableProductLinkManagement
     *       set to invisible. See if we can't streamline this without passing an "adjustVisibility"
     *       paramter through a bunch of classes...
     *
     * @param ProductInterface $magentoProduct
     */
    private function overrideVisibilityForExistingProduct(ProductInterface $magentoProduct)
    {
        $currentVisibility = $magentoProduct->getVisibility();

        if (Visibility::VISIBILITY_NOT_VISIBLE === $currentVisibility) {
            $magentoProduct->setVisibility(Visibility::VISIBILITY_BOTH);
        }
    }

    private function mapName(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $syncStrategy = $this->productConfig->getNameSyncStrategy();

        // If the product is new or we always sync the name, we'll sync it now
        if (!$magentoProduct->getId() || $syncStrategy->sameValueAs(SyncStrategy::get('always'))) {
            $magentoProduct->setName((string) $skyLinkProduct->getName());
        }
    }

    private function mapPrices(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        // Setup pricing for product
        $magentoProduct->setPrice($skyLinkProduct->getPricingStructure()->getRegularPrice()->toNative());

        $magentoProduct->unsetData('special_price');
        $magentoProduct->unsetData('special_from_date');
        $magentoProduct->unsetData('special_to_date');

        if (false === $skyLinkProduct->getPricingStructure()->hasSpecialPrice()) {
            $this->removeSpecialPrices($magentoProduct);
            return;
        }

        $skyLinkSpecialPrice = $skyLinkProduct->getPricingStructure()->getSpecialPrice();

        // If the end date is before now, we do not need to put a new special price on at all, as
        // it cannot end in the past. In fact, Magento will let you save it, but won't let
        // subsequent saves from the admin interface occur.
        $now = new DateTimeImmutable();
        if ($skyLinkSpecialPrice->hasEndDate() && $skyLinkSpecialPrice->getEndDate() < $now) {
            $this->removeSpecialPrices($magentoProduct);
            return;
        }

        $magentoProduct->setCustomAttribute('special_price', $skyLinkSpecialPrice->getPrice()->toNative());

        // If there's a start date at least now or in the future, we'll use that...
        if ($skyLinkSpecialPrice->hasStartDate() && $skyLinkSpecialPrice->getStartDate() >= $now) {
            $magentoProduct->setCustomAttribute(
                'special_from_date',
                $this->dateTimeToLocalisedAttributeValue($skyLinkSpecialPrice->getStartDate())
            );

            // Otherwise, we'll use a start date from now
        } else {
            $magentoProduct->setCustomAttribute('special_from_date', $this->dateTimeToLocalisedAttributeValue($now));
        }

        // If there's an end date, we'll just use that
        if ($skyLinkSpecialPrice->hasEndDate()) {
            $magentoProduct->setCustomAttribute(
                'special_to_date',
                $this->dateTimeToLocalisedAttributeValue($skyLinkSpecialPrice->getEndDate())
            );

            // Otherwise, it's indefinite
        } else {
            $magentoProduct->setCustomAttribute('special_to_date', null);
        }
    }

    private function removeSpecialPrices(ProductInterface $magentoProduct)
    {
        $magentoProduct->setCustomAttribute('special_price', null);
        $magentoProduct->setCustomAttribute('special_from_date', null);
        $magentoProduct->setCustomAttribute('special_to_date', null);
    }

    private function mapCustomerGroupPrices(
        ProductInterface $magentoProduct,
        SkyLinkProduct $skyLinkProduct,
        WebsiteInterface $magentoWebsite = null
    ) {
        $magentoWebsiteId = isset($magentoWebsite) ? $magentoWebsite->getId() : 0;

        // We'll loop through all of the price group prices
        // @todo what about orphaned prices? Deleted stores? IDK
        array_map(function (SkyLinkPriceGroupPrice $skyLinkPriceGroupPrice) use ($magentoProduct, $magentoWebsiteId) {

            /* @var \Magento\Customer\Api\Data\GroupInterface|null $magentoCustomerGroup */
            $magentoCustomerGroup = $this
                ->magentoCustomerGroupRepository
                ->findBySkyLinkPriceGroupKey($skyLinkPriceGroupPrice->getKey());

            if (null === $magentoCustomerGroup) {
                throw CustomerGroupNotSyncedException::withSkyLinkPriceGroupKey($skyLinkPriceGroupPrice->getKey());
            }

            // Grab our tier prices
            $tierPrices = $magentoProduct->getData('tier_price') ?: [];

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
            $tierPrice['price'] = $tierPrice['website_price']  = $skyLinkPriceGroupPrice->getPrice()->toNative();

            if (count($matching) > 0) {
                $tierPrices[key($matching)] = $tierPrice;
            } else {
                $tierPrices[] = $tierPrice;
            }

            $magentoProduct->setData('tier_price', $tierPrices);
        }, $skyLinkProduct->getPricingStructure()->getPriceGroupPrices());
    }

    /**
     * @ Until we extend the stock item itself, we are storing additional quantities on order against the product itself
     */
    private function mapQuantities(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        $magentoProduct->unsetData('qty_available');
        $magentoProduct->setCustomAttribute('qty_available', $skyLinkProduct->getInventoryItem()->getQtyAvailable()->toNative());

        $magentoProduct->unsetData('qty_on_order');
        if ($skyLinkProduct->getInventoryItem()->hasQtyOnOrder()) {
            $magentoProduct->setCustomAttribute('qty_on_order', $skyLinkProduct->getInventoryItem()->getQtyOnOrder()->toNative());
        } else {
            $magentoProduct->setCustomAttribute('qty_on_order', 0);
        }
    }

    private function mapAttributes(ProductInterface $magentoProduct, SkyLinkProduct $skyLinkProduct)
    {
        foreach (SkyLinkAttributeCode::getConstants() as $skyLinkAttributeCodeString) {
            $skyLinkAttributeCode = SkyLinkAttributeCode::get($skyLinkAttributeCodeString);

            /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface $magentoAttribute */
            $magentoAttribute = $this->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);

            /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption $skyLinkAttributeOption */
            $skyLinkAttributeOption = $skyLinkProduct->getAttributeOption($skyLinkAttributeCode);

            // If there's no value for the SkyLink Attribute Option, we'll wipe the custom attribute value
            if (null === $skyLinkAttributeOption) {
                $magentoProduct->unsetData($magentoAttribute->getAttributeCode());
                $magentoProduct->setCustomAttribute($magentoAttribute->getAttributeCode(), null);
                continue;
            }

            /* @var \RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType $magentoAttributeType */
            $magentoAttributeType = $this->attributeTypeManager->getType($magentoAttribute);

            // If we use options, we'll grab the mapped option
            if ($magentoAttributeType->usesOptions()) {
                $magentoAttributeValue = $this
                    ->getMagentoAttributeOptionFromSkyLinkAttributeOption($skyLinkAttributeOption)
                    ->getValue();
                // Otherweise, we'll use the label for the SkyLink Attribute Option
            } else {
                $magentoAttributeValue = $skyLinkAttributeOption->getLabel()->toNative();
            }

            // Now we'll set the custom attribute value
            $magentoProduct->unsetData($magentoAttribute->getAttributeCode());
            $magentoProduct->setCustomAttribute(
                $magentoAttribute->getAttributeCode(),
                $magentoAttributeValue
            );
        }
    }

    /**
     * Get the Magento Attribute for the given SkyLink Attribute Code.
     *
     * @param SkyLinkAttributeCode   $skyLinkAttributeCode
     * @param SkyLinkAttributeOption $skyLinkAttributeOption
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     *
     * @throws AttributeNotMappedException       When there is no Attribute mapping
     */
    private function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skyLinkAttributeCode)
    {
        /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface|null $magentoAttribute */
        $magentoAttribute = $this
            ->attributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);

        // If we can't find a corresponding Magento Attribute, we can't continue mapping the product
        if (null === $magentoAttribute) {
            throw AttributeNotMappedException::withSkyLinkAttributeCode($skyLinkAttributeCode);
        }

        return $magentoAttribute;
    }

    /**
     * Get the Magento Attribute Option for the given SkyLink Attribute Option.
     *
     * @param SkyLinkAttributeOption $skyLinkAttributeOption
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface
     *
     * @throws AttributeOptionNotMappedException When there is no Attribute Option mapping
     */
    private function getMagentoAttributeOptionFromSkyLinkAttributeOption(
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $magentoAttributeOption = $this
            ->attributeOptionRepository
            ->getMappedMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

        if (null === $magentoAttributeOption) {
            throw AttributeOptionNotMappedException::withSkyLinkAttributeOption($skyLinkAttributeOption);
        }

        return $magentoAttributeOption;
    }

    /**
     * Takes the given DateTime and compares it against the current DateTime. If it's
     * less, we'll modify it to be at least the current DateTime, then we'll
     * format it in the required Timezone in the required format.
     *
     * @return string
     */
    private function dateTimeToLocalisedAttributeValue(DateTimeImmutable $date)
    {
        $date = $date->setTimezone(new DateTimeZone($this->timezone->getConfigTimezone()));

        return $this->dateTime->formatDate($date->getTimestamp());
    }
}
