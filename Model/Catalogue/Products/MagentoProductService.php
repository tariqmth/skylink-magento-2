<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\AlreadyExistsException;

trait MagentoProductService
{
    /**
     * The Base Magento Product Repository instance.
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $baseMagentoProductRepository;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * Right now there does not appear to be a way to set the URL of a product
     * prior to saving it. Therefore, we'll just try save the product and if
     * an error occurs, we'll just append an incrementing digit to the key.
     *
     * @link \Mage\Catalog\Model\Product\Copier::copy()
     *
     * @todo work out how to get past testing as setUrlKey() does not exist on ProductInterface
     * @todo refactor because this is incrementing the ID every attempt
     *
     * @param ProductInterface $magentoProduct
     */
    private function save(ProductInterface $magentoProduct)
    {
        $productIsSaved = false;
        $counter = 0;

        do {
            try {

                // The first time we try save the product
                if ($counter > 0) {
                    $urlKey = $magentoProduct->getUrlKey();
                    var_dump(['Existing URL KEY' => $urlKey]);

                    // If the URL key ends in a number, we'll increment it
                    if (preg_match('/(.*)-(\d+)$/', $urlKey, $matches)) {
                        $urlKey = sprintf('%s-%d', $matches[1], $matches[2] + 1);

                    // Otherwise, we'll append a "1"
                    } else {
                        $urlKey .= '-1';
                    }

                    $magentoProduct->setUrlKey($urlKey);
                } else {
                    $magentoProduct->setUrlKey($this->productUrlPathGenerator->getUrlKey($magentoProduct));
                }

                $counter++;
                $this->baseMagentoProductRepository->save($magentoProduct);
                $productIsSaved = true;
            } catch (AlreadyExistsException $e) {
                // This is thrown when the URL key already exists, keep on looping
            }
        } while ($productIsSaved === false);
    }
}
