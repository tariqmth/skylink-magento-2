<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Model\Product\UrlFactory as ProductUrlFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\UrlKeyGeneratorInterface;
use RetailExpress\SkyLink\Model\Catalogue\Products\ProductInterfaceAsserter;

class UrlKeyGenerator implements UrlKeyGeneratorInterface
{
    use ProductInterfaceAsserter;

    private $productConfig;

    private $productUrlFactory;

    private $productUrl;

    private $baseMagentoProductRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        ConfigInterface $productConfig,
        ProductUrlFactory $productUrlFactory,
        ProductRepositoryInterface $baseMagentoProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productConfig = $productConfig;
        $this->productUrlFactory = $productUrlFactory;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function generateUniqueUrlKeyForMagentoProduct(ProductInterface $magentoProduct)
    {
        $this->assertImplementationOfProductInterface($magentoProduct);

        $attributeCodes = $this->productConfig->getUrlKeyAttributeCodes();

        $attributeValues = array_filter(array_map(function ($attributeCode) use ($magentoProduct) {
            $attributeValue = $magentoProduct->getAttributeText($attributeCode);

            if (false === $attributeValue) {
                $attributeValue = $magentoProduct->getData($attributeCode);
            }

            if ($attributeValue) {
                return $attributeValue;
            }
        }, $attributeCodes));

        $i = 0;
        do {
            $urlKey = $this->generateUrlKey($attributeValues, $i++);
        } while ($this->productExistsWithUrlKey($magentoProduct, $urlKey));

        return $urlKey;
    }

    private function generateUrlKey(array $urlKeyParts, $counter)
    {
        $urlKey = implode(' ', $urlKeyParts);

        if ($counter > 0) {
            $urlKey .= sprintf(' %d', $counter);
        }

        return $this->getProductUrl()->formatUrlKey($urlKey);
    }

    private function productExistsWithUrlKey(ProductInterface $excludedMagentoProduct, $urlKey)
    {
        // Make sure we don't include the same product
        $this->searchCriteriaBuilder->addFilter('sku', $excludedMagentoProduct->getSku(), 'neq');

        // Filter by the given URL key
        $this->searchCriteriaBuilder->addFilter('url_key', $urlKey);

        /* @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /* @var \\Magento\Catalog\Api\Data\ProductSearchResultsInterface $matchingProducts */
        $matchingProducts = $this->baseMagentoProductRepository->getList($searchCriteria);

        return 1 === $matchingProducts->getTotalCount();
    }

    /**
     * @return \Magento\Catalog\Model\Product\Url
     */
    private function getProductUrl()
    {
        if (null === $this->productUrl) {
            $this->productUrl = $this->productUrlFactory->create();
        }

        return $this->productUrl;
    }
}
