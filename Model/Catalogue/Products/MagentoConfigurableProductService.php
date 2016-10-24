<?php

namespace RetailExpress\SkyLink\Model\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductServiceInterface;

class MagentoConfigurableProductService implements MagentoConfigurableProductServiceInterface
{
    private $magentoLinkManagement;

    public function __construct(LinkManagementInterface $magentoLinkManagement)
    {
        $this->magentoLinkManagement = $magentoLinkManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function syncChildren(ProductInterface $configurableProduct, array $simpleProducts)
    {
        // Cache our configurable product's SKU
        $configurableProductSku = $configurableProduct->getSku();

        // Get an array of new SKUs
        $newSkus = array_map(function (ProductInterface $simpleProduct) {
            return $simpleProduct->getSku();
        }, $simpleProducts);

        // And an array of existing SKUs
        $existingSkus = $this->magentoLinkManagement->getChildren($configurableProductSku);

        // Determine what to add and remove
        $skusToAdd = array_diff($newSkus, $existingSkus);
        $skusToRemove = array_diff($existingSkus, $newSkus);

        // Add the new SKUs
        array_walk($skusToAdd, function ($skuToAdd) use ($configurableProductSku) {
            $this->magentoLinkManagement->addChild($configurableProductSku, $skuToAdd);
        });

        // Remove all old SKUs
        array_walk($skusToRemove, function ($skuToRemove) use ($configurableProductSku) {
            $this->magentoLinkManagement->removeChild($configurableProductSku, $skuToRemove);
        });
    }
}
