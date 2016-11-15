<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface as BaseLinkManagementInterface;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory as ConfigurableProductTypeFactory;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Exceptions\Products\TooManyParentProductsException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\Attribute as SkyLinkAttribute;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy as SkyLinkMatrixPolicy;

class MagentoConfigurableProductLinkManagement implements MagentoConfigurableProductLinkManagementInterface
{
    private $configurableProductTypeFactory;

    private $baseMagentoLinkManagement;

    private $productExtensionFactory;

    private $optionFactory;

    private $optionValueFactory;

    private $magentoAttributeRepository;

    public function __construct(
        ConfigurableProductTypeFactory $configurableProductTypeFactory,
        BaseLinkManagementInterface $baseMagentoLinkManagement,
        ProductExtensionFactory $productExtensionFactory,
        OptionInterfaceFactory $optionFactory,
        OptionValueInterfaceFactory $optionValueFactory,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository
    ) {
        $this->configurableProductTypeFactory = $configurableProductTypeFactory;
        $this->baseMagentoLinkManagement = $baseMagentoLinkManagement;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->optionFactory = $optionFactory;
        $this->optionValueFactory = $optionValueFactory;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentProductId(ProductInterface $childProduct)
    {
        /* @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType */
        $configurableProductType = $this->configurableProductTypeFactory->create();

        // Grab our parent IDs
        $parentIds = $configurableProductType->getParentIdsByChild($childProduct->getId());
        $parentIdsCount = count($parentIds);

        // If a child has been assigned to multiple parents, we can't possibly determine which
        // one to use, we'll just throw an exception and the user can resolve this manually.
        if ($parentIdsCount > 1) {
            throw TooManyParentProductsException::withChildProduct($childProduct, $parentIdsCount);
        }

        return current($parentIds);
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
    }

    private function getProductExtensionAttributes(ProductInterface $parentProduct)
    {
        /* @var |null $extendedAttributes */
        $extendedAttributes = $parentProduct->getExtensionAttributes();

        if (null === $extendedAttributes) {

            /* @var ProductExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->productExtensionFactory->create();
            $parentProduct->setExtensionAttributes($extendedAttributes);
        }

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
        $magentoAttributes = array_map(function (SkyLinkAttribute $skyLinkAttribute) {

            /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode[] $skyLinkAttributeCodes */
            $skyLinkAttributeCode = $skyLinkAttribute->getCode();

            return $this
                ->magentoAttributeRepository
                ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);
        }, $skyLinkMatrixPolicy->getAttributes());

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
        /* @var OptionInterface[] $existingOptions */
        $existingOptions = $extendedAttributes->getConfigurableProductOptions();

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
}
