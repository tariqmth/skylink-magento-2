<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Model\Product\UrlFactory as ProductUrlFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Api\Catalogue\Products\UrlKeyGeneratorInterface;

class UrlKeyGenerator implements UrlKeyGeneratorInterface
{
    private $productUrlFactory;

    private $baseMagentoProductRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        ProductUrlFactory $productUrlFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productUrlFactory = $productUrlFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function generateUniqueUrlKeyForMagentoProduct(ProductInterface $magentoProduct)
    {
        /* @var \Magento\Catalog\Model\Product\Url $productUrl */
        $productUrl = $this->productUrlFactory->create();

        $urlKey = $this->generateUrlKeyBasedOnProductName($magentoProduct, $productUrl);

        // Try find an existing product with the given URL key, and if so, we'll
        // generate one based on the name and the SKU (which is always unique).
        if ($this->productExistsWithUrlKey($urlKey)) {
            $urlKey = $this->generateUrlKeyBasedOnProductNameAndSku($magentoProduct, $productUrl);
        }

        return $urlKey;
    }

    private function generateUrlKeyBasedOnProductName(ProductInterface $magentoProduct, ProductUrl $productUrl)
    {
        return $productUrl->formatUrlKey($magentoProduct->getName());
    }

    private function generateUrlKeyBasedOnProductNameAndSku(ProductInterface $magentoProduct, ProductUrl $productUrl)
    {
        return $productUrl->formatUrlKey(sprintf(
            '%s %s',
            $magentoProduct->getName(),
            $magentoProduct->getSku()
        ));
    }

    private function productExistsWithUrlKey($urlKey)
    {
        $this->searchCriteriaBuilder->addFilter('url_key', $urlKey);

        /* @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /* @var \\Magento\Catalog\Api\Data\ProductSearchResultsInterface $matchingProducts */
        $matchingProducts = $this->baseMagentoProductRepository->getList($searchCriteria);

        return 1 === $matchingProducts->getTotalCount();
    }
}
