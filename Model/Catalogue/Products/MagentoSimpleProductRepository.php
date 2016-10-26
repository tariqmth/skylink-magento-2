<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface as ProductConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException;

class MagentoSimpleProductRepository implements MagentoSimpleProductRepositoryInterface
{
    private $baseMagentoProductRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        ProductRepositoryInterface $baseMagentoProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySkyLinkProductId(SkyLinkProductId $skyLinkProductId)
    {
        // Search simple products by the given SkyLink Product ID
        $this->searchCriteriaBuilder->addFilter('type_id', ProductType::TYPE_SIMPLE);
        $this->searchCriteriaBuilder->addFilter('skylink_product_id', (string) $skyLinkProductId);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $existingProducts = $this->baseMagentoProductRepository->getList($searchCriteria);
        $existingProductMatches = $existingProducts->getTotalCount();

        if ($existingProductMatches > 1) {
            throw TooManyProductMatchesException::withSkyLinkProductId($skyLinkProductId, $existingProductMatches);
        }

        if ($existingProductMatches === 1) {
            return current($existingProducts->getItems());
        }
    }
}
