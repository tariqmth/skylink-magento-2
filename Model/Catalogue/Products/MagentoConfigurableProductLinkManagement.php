<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
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
        // Grab the extension attributes instance
        $productExtensionAtributes = $this->getProductExtensionAttributes($parentProduct);

        // Firstly, let's set the links for the configurable product
        $productExtensionAtributes
            ->setConfigurableProductLinks($this->getConfigurableProductLinks($childrenProducts));

        // Now set the configurable product options
        $productExtensionAtributes
            ->setConfigurableProductOptions($this->getConfigurableProductOptions(
                $skyLinkMatrixPolicy,
                $childrenProducts
            ));
    }

    private function getProductExtensionAttributes(ProductInterface $parentProduct)
    {
        /* @var \Magento\Catalog\Api\Data\ProductExtensionInterface|null $productExtensionAtributes */
        $productExtensionAtributes = $parentProduct->getExtensionAttributes();

        if (null === $productExtensionAtributes) {

            /* @var \Magento\Catalog\Api\Data\ProductExtensionInterface $productExtensionAtributes */
            $productExtensionAtributes = $this->productExtensionFactory->create();
            $parentProduct->setExtensionAttributes($productExtensionAtributes);
        }

        return $productExtensionAtributes;
    }

    private function getConfigurableProductLinks(array $childrenProducts)
    {
        return array_map(function (ProductInterface $childProduct) {
            return $childProduct->getId();
        }, $childrenProducts);
    }

    private function getConfigurableProductOptions(SkyLinkMatrixPolicy $skyLinkMatrixPolicy, array $childrenProducts)
    {
        // Firstly, look at the attributes in the SkyLink Matrix Policy. Use our own Attributes Repository to find
        // the corresponding Magento Attributes. We'll then grab the values of those attributes in the given
        // children products and voila, we have the data we need to set the configurable product options!

        /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface[] $magentoAttributes */
        $magentoAttributes = array_map(function (SkyLinkAttribute $skyLinkAttribute) {

            /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode[] $skyLinkAttributeCodes */
            $skyLinkAttributeCode = $skyLinkAttribute->getCode();

            return $this
                ->magentoAttributeRepository
                ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);
        }, $skyLinkMatrixPolicy->getAttributes());

        $options = [];
        $i = 0;

        array_walk($magentoAttributes, function ($magentoAttibute) use (&$options, &$i, $childrenProducts) {
            $option = $this->optionFactory->create();
            $option->setAttributeId($magentoAttibute->getAttributeId());
            $option->setLabel($magentoAttibute->getDefaultFrontendLabel()); // @todo, should this be scoped?
            $option->setValues([]);
            $options[] = $option;

            array_walk($childrenProducts, function (ProductInterface $childProduct) use ($magentoAttibute, $option) {
                $optionValue = $this->optionValueFactory->create();

                $attributeValue = $childProduct->getCustomAttribute($magentoAttibute->getAttributeCode());

                // Ready for a tongue-twister?
                $optionValue->setValueIndex($attributeValue->getValue());

                $option->setValues(array_merge($option->getValues(), [$optionValue]));
            });
        });

        return $options;
    }
}
