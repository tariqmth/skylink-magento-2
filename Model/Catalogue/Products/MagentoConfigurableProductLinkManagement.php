<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Exceptions\Products\TooManyParentProductsException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy as SkyLinkMatrixPolicy;

class MagentoConfigurableProductLinkManagement implements MagentoConfigurableProductLinkManagementInterface
{
    private $productExtensionFactory;

    private $optionFactory;

    private $optionValueFactory;

    private $magentoAttributeRepository;

    private $baseMagentoProductRepository;

    public function __construct(
        ProductExtensionFactory $productExtensionFactory,
        OptionInterfaceFactory $optionFactory,
        OptionValueInterfaceFactory $optionValueFactory,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository,
        ProductRepositoryInterface $baseMagentoProductRepository
    ) {
        $this->productExtensionFactory = $productExtensionFactory;
        $this->optionFactory = $optionFactory;
        $this->optionValueFactory = $optionValueFactory;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function linkChildren(
        SkyLinkMatrixPolicy $skyLinkMatrixPolicy,
        ProductInterface $parentProduct,
        array $childrenProducts
    ) {
        // Grab an extension attributes instance
        $extendedAttributes = $this->getProductExtensionAttributes($parentProduct);

        // Attach the new configurable product links
        $extendedAttributes->setConfigurableProductLinks($this->getConfigurableProductLinks($childrenProducts));

        // Grab our new options and update the model accordingly
        $configurableProductOptions = $this->getConfigurableProductOptions($skyLinkMatrixPolicy, $childrenProducts);
        $this->updateExistingConfigurableProductOptions($extendedAttributes, $configurableProductOptions);
        $this->makeChildrenInvisible($childrenProducts);
    }

    private function getProductExtensionAttributes(ProductInterface $parentProduct)
    {
        /* @var |null $extendedAttributes */
        $extendedAttributes = $parentProduct->getExtensionAttributes();

        // @todo Move the setting of extension attributes back into this "if" statement.
        // Magento\Catalog\Model\Product::getExtensionAttributes() always returns an
        // instance it seems, however unlike other implementations does not bind
        // the instance to the product, meaning it'll never actually do
        // anything with the extension attributes we provide to it.
        // It's inconsistent with the contarct too, which sucks.
        // This is present in Magento 2.1, not 2.0.
        if (null === $extendedAttributes) {
            /* @var ProductExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->productExtensionFactory->create();
        }
        $parentProduct->setExtensionAttributes($extendedAttributes);

        return $extendedAttributes;
    }

    private function getConfigurableProductLinks(array $childrenProducts)
    {
        // Return the ID from each product
        return array_map(function (ProductInterface $childProduct) {
            return $childProduct->getId();
        }, $childrenProducts);
    }

    private function getConfigurableProductOptions(SkyLinkMatrixPolicy $skyLinkMatrixPolicy, array $childrenProducts)
    {
        /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface[] $magentoAttributes */
        $magentoAttributes = array_map(function (SkyLinkAttributeCode $skyLinkAttributeCode) {
            return $this
                ->magentoAttributeRepository
                ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);
        }, $skyLinkMatrixPolicy->getAttributeCodes());

        // Transform each product attribute into a configurable product option
        return array_map(function ($magentoAttibute) use (&$options, $childrenProducts) {

            /* @var OptionInterface $option */
            $option = $this->optionFactory->create();
            $option->setAttributeId($magentoAttibute->getAttributeId());
            $option->setLabel($magentoAttibute->getDefaultFrontendLabel()); // @todo, should this be scoped?
            $option->setValues([]);

            array_walk($childrenProducts, function (ProductInterface $childProduct) use ($magentoAttibute, $option) {
                $optionValue = $this->optionValueFactory->create();

                $attributeValue = $childProduct->getCustomAttribute($magentoAttibute->getAttributeCode());

                // Ready for a tongue-twister?
                $optionValue->setValueIndex($attributeValue->getValue());

                $option->setValues(array_merge($option->getValues(), [$optionValue]));
            });

            return $option;
        }, $magentoAttributes);
    }

    private function updateExistingConfigurableProductOptions(
        ProductExtensionInterface $extendedAttributes,
        array $newOptions
    ) {
        /* @var OptionInterface[]|null $existingOptions */
        $existingOptions = $extendedAttributes->getConfigurableProductOptions();

        if (null === $existingOptions) {
            $existingOptions = [];
        }

        // Determine the final options by iterating through new options
        // and supplying a combination of the existing options.
        $finalOptions = array_map(function (OptionInterface $newOption) use ($existingOptions) {
            $existingOption = $this->findExistingConfigurableProductOptionForNewOption($newOption, $existingOptions);

            if (null === $existingOption) {
                return $newOption;
            }

            // Override the values of the existing option
            $existingOption->setValues($newOption->getValues());

            return $existingOption;
        }, $newOptions);

        $extendedAttributes->setConfigurableProductOptions($finalOptions);
    }

    private function findExistingConfigurableProductOptionForNewOption(
        OptionInterface $newOption,
        array $existingOptions
    ) {
        $matching = array_filter($existingOptions, function (OptionInterface $existingOption) use ($newOption) {

            // Sometimes we have a string, sometimes we have an integer
            return $existingOption->getAttributeId() == $newOption->getAttributeId();
        });

        if (count($matching) === 1) {
            return current($matching); // @todo check if there's 2 matching? Not sure Magento could let that
        }
    }

    private function makeChildrenInvisible(array $childrenProducts)
    {
        array_walk($childrenProducts, function (ProductInterface $childProduct) {
            $currentVisibility = $childProduct->getVisibility();

            if (Visibility::VISIBILITY_NOT_VISIBLE == $currentVisibility) { // Non-strict comparison
                return;
            }

            $childProduct->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
            $this->baseMagentoProductRepository->save($childProduct);
        });
    }
}
