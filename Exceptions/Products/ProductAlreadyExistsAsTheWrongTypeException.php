<?php

namespace RetailExpress\SkyLink\Exceptions\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\SimpleProduct;

class ProductAlreadyExistsAsTheWrongTypeException extends LocalizedException
{
    /**
     * @var Product
     */
    private $skyLinkProduct;

    /**
     * @var ProductInterface
     */
    private $magentoProduct;

    public static function withMatrix(Matrix $skyLinkMatrix, ProductInterface $magentoProduct)
    {
        $message = __(
            'A SkyLink Matrix with a Manufacturer SKU (which is used as the configurable product SKU) "%1" matches an existing Magento %2 product and therefore cannot be used to create a new Magento configurable product.',
            $skyLinkMatrix->getSku(),
            $magentoProduct->getTypeId()
        );

        return self::create($message, $skyLinkMatrix, $magentoProduct);
    }

    public static function withSimpleProduct(SimpleProduct $skyLinkProduct, ProductInterface $magentoProduct)
    {
        $message = __(
            'A SkyLink Product with SKU "%1" matches an existing Magento %2 product and therefore cannot be used to create a new Magento simple product.',
            $skyLinkProduct->getSku(),
            $magentoProduct->getTypeId()
        );

        return self::create($message, $skyLinkProduct, $magentoProduct);
    }

    private static function create(Phrase $message, Product $skyLinkProduct, ProductInterface $magentoProduct)
    {
        $exception = new self($message);

        $exception->skyLinkProduct = $skyLinkProduct;
        $exception->magentoProduct = $magentoProduct;

        return $exception;
    }

    public function getSkyLinkProduct()
    {
        return $this->skyLinkProduct;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }
}
