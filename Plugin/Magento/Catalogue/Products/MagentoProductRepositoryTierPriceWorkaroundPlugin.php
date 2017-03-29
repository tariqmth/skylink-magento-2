<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use RetailExpress\SkyLink\Model\Catalogue\Products\ProductInterfaceAsserter;

class MagentoProductRepositoryTierPriceWorkaroundPlugin
{
    use ProductInterfaceAsserter;

    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $magentoProduct
    ) {
        $this->assertImplementationOfProductInterface($magentoProduct);

        if (!is_array($magentoProduct->getData('tier_price'))) {
            $magentoProduct->setData('tier_price', []);
        }

        return $magentoProduct;
    }
}
