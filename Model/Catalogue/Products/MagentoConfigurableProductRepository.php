<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductRepositoryInterface;
use ValueObjects\StringLiteral\StringLiteral;

class MagentoConfigurableProductRepository implements MagentoConfigurableProductRepositoryInterface
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

    public function findBySkyLinkManufacturerSku(StringLiteral $skyLinkManufacturerSku)
    {
        $this->searchCriteriaBuilder->addFilter('type_id', ConfigurableType::TYPE_CODE);
        $this->searchCriteriaBuilder->addFilter('manufacturer_sku', (string) $skyLinkManufacturerSku);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $existingProducts = $this->baseMagentoProductRepository->getList($searchCriteria);
        $existingProductMatches = $existingProducts->getTotalCount();

        if ($existingProductMatches > 1) {
            throw TooManyProductMatchesException::withSkyLinkManufacturerSku(
                $skyLinkManufacturerSku,
                $existingProductMatches
            );
        }

        if ($existingProductMatches === 1) {
            return $this->baseMagentoProductRepository->getById(current($existingProducts->getItems())->getId());
        }
    }
}
