<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory as ConfigurableProductTypeFactory;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Exceptions\Products\TooManyParentProductsException;

class MagentoConfigurableProductLinkManagement implements MagentoConfigurableProductLinkManagementInterface
{
    private $configurableProductTypeFactory;

    public function __construct(ConfigurableProductTypeFactory $configurableProductTypeFactory)
    {
        $this->configurableProductTypeFactory = $configurableProductTypeFactory;
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
    public function syncChildren(ProductInterface $parentProduct, array $childrenProducts)
    {
        //
    }
}
