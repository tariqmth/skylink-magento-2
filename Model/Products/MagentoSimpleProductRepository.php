<?php

namespace RetailExpress\SkyLink\Model\Products;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Api\Products\ConfigInterface as ProductConfigInterface;
use RetailExpress\SkyLink\Api\Products\MagentoProductLinkRepositoryInterface;
use RetailExpress\SkyLink\Api\Products\MagentoProductRepositoryInterface;
use RetailExpress\SkyLink\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException;

class MagentoSimpleProductRepository implements MagentoProductRepositoryInterface
{
    private $baseMagentoProductRepository;

    private $searchCriteriaBuilder;

    private $magentoProductLinkRepository;

    public function __construct(
        ProductRepositoryInterface $baseMagentoProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MagentoProductLinkRepositoryInterface $magentoProductLinkRepository
    ) {
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->magentoProductLinkRepository = $magentoProductLinkRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findProductBySkyLinkProductId(SkyLinkProductId $skyLinkProductId)
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
            return $existingProducts->getItems()[0];
        }
    }
}
