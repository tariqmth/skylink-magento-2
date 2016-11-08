<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface as ProductConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductLinkManagementInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoConfigurableProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class MagentoConfigurableProductRepository implements MagentoConfigurableProductRepositoryInterface
{
    private $magentoSimpleProductRepository;

    private $magentoConfigurableProductLinkManagement;

    private $productConfig;

    private $baseMagentoProductRepository;

    public function __construct(
        MagentoSimpleProductRepositoryInterface $magentoSimpleProductRepository,
        MagentoConfigurableProductLinkManagementInterface $magentoConfigurableProductLinkManagement,
        ProductConfigInterface $productConfig,
        ProductRepositoryInterface $baseMagentoProductRepository
    ) {
        $this->magentoSimpleProductRepository = $magentoSimpleProductRepository;
        $this->magentoConfigurableProductLinkManagement = $magentoConfigurableProductLinkManagement;
        $this->productConfig = $productConfig;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySkyLinkProductIds(array $skyLinkProductIds)
    {
        $childrenProducts = $this->findSimpleProductsBySkyLinkProductIds($skyLinkProductIds);

        if (0 === count($childrenProducts)) {
            return null;
        }

        $parentIdsToOccurances = $this->getSortedParentIdsToOccurances($childrenProducts);

        if (0 === count($parentIdsToOccurances)) {
            return null;
        }

        $parentIdThatMeetsThreshold = $this->getFirstParentIdToMeetThreshold($parentIdsToOccurances);

        if (null !== $parentIdThatMeetsThreshold) {
            return $this->baseMagentoProductRepository->getById($parentIdThatMeetsThreshold);
        }
    }

    private function findSimpleProductsBySkyLinkProductIds(array $skyLinkProductIds)
    {
        $magentoSimpleProducts = array_map(function (SkyLinkProductId $skyLinkProductId) {
            return $this->magentoSimpleProductRepository->findBySkyLinkProductId($skyLinkProductId);
        }, $skyLinkProductIds);

        return array_values(array_filter($magentoSimpleProducts));
    }

    private function getSortedParentIdsToOccurances(array $childrenProducts)
    {
        // Grab the parent IDs
        $parentIds = $this->getParentIds($childrenProducts);

        // Transform the array into parent ID => occurances
        $parentIdsToOccurances = array_count_values($parentIds);

        // Sort the occurances by largest first, maintaining key (in this case, parent ID) association
        arsort($parentIdsToOccurances);

        return $parentIdsToOccurances;
    }

    private function getParentIds(array $childrenProducts)
    {
        $parentIds = array_map(function (ProductInterface $childProduct) {
            return $this->magentoConfigurableProductLinkManagement->getParentProductId($childProduct);
        }, $childrenProducts);

        return array_values(array_filter($parentIds));
    }

    private function getFirstParentIdToMeetThreshold(array $parentIdsToOccurances)
    {
        // Determine the total occurances within the IDs to occurances array, note that
        // this may not actually match the total number of products passed in, but what
        // previously existed (this is by design that this is the way it works).
        $totalOccurances = array_sum($parentIdsToOccurances);

        $threshold = $this->productConfig->getConfigurableProductMatchThreshold();

        foreach ($parentIdsToOccurances as $parentId => $occurances) {
            $occurancesAsDecimal = $occurances / $totalOccurances;

            // Compare our occurance against the configured threshold
            if ($occurancesAsDecimal < $threshold->toNative()) {
                continue;
            }

            return $parentId;
        }
    }
}
