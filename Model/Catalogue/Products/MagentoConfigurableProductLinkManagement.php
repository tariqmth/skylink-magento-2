<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\ConfigurableProduct\Api\Data\OptionInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface as BaseLinkManagementInterface;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory as ConfigurableProductTypeFactory;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Exceptions\Products\TooManyParentProductsException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy as SkyLinkMatrixPolicy;

class MagentoConfigurableProductLinkManagement implements MagentoConfigurableProductLinkManagementInterface
{
    private $configurableProductTypeFactory;

    private $baseMagentoLinkManagement;

    private $productExtensionFactory;

    private $optionFactory;

    private $optionValueFactory;

    public function __construct(
        ConfigurableProductTypeFactory $configurableProductTypeFactory,
        BaseLinkManagementInterface $baseMagentoLinkManagement,
        ProductExtensionFactory $productExtensionFactory,
        OptionInterfaceFactory $optionFactory,
        OptionValueInterfaceFactory $optionValueFactory
    ) {
        $this->configurableProductTypeFactory = $configurableProductTypeFactory;
        $this->baseMagentoLinkManagement = $baseMagentoLinkManagement;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->optionFactory = $optionFactory;
        $this->optionValueFactory = $optionValueFactory;
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

        // Now, we'll grab all of the options and values from our simple products
        $configurableProductOptions = [$this->optionFactory->create()];

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
    }
}
