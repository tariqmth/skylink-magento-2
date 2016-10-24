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

class MagentoProductRepository implements MagentoProductRepositoryInterface
{
    private $baseMagentoProductRepository;

    private $magentoProductLinkRepository;

    private $productConfig;

    public function __construct(
        ProductRepositoryInterface $baseMagentoProductRepository,
        MagentoProductLinkRepositoryInterface $magentoProductLinkRepository,
        ProductConfigInterface $productConfig
    ) {
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->magentoProductLinkRepository = $magentoProductLinkRepository;
        $this->productConfig = $productConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function findProductBySkyLinkProductIds(array $skyLinkProductIds)
    {
        // Grab our simple products
        $childrenProducts = array_map(function (SkyLinkProductId $skyLinkProductId) {
            return $this->findSimpleProductBySkyLinkProductId($skyLinkProductId);
        }, $skyLinkProductIds);

        // If there's no simple products that match our SkyLink Product IDs, we do not
        // need to go further than this.
        if ($childrenProducts->getTotalCount() === 0) {
            return;
        }

        // Now we have a list of simple products, let's use our own implementation that
        // searches for parent configurable products of the child products we found.
        $parentProductIds = array_map(function (ProductInterface $childProduct) {
            return $this->magentoProductLinkRepository->getParentProductId($childProduct->getId());
        }, $childrenProducts->getItems());

        // Now we'll filter out any empty results and use the Product ID
        // as the key and it's occurance count as the value
        $idsToOccurances = array_count_values($parentProductIds);

        // We'll then select the first product who is over the designed threshold
        $matchThreshold = $this->productConfig->getConfigurableProductMatchThreshold();
        $skyLinkProductIdsCount = count($skyLinkProductIds);

        foreach ($idsToOccurances as $id => $occurance) {
            $occuranceAsDecimal = $occurance / $skyLinkProductIdsCount;

            // If our occurance is at least the threshold, then we'll return
            // that product from Magento
            if ($occuranceAsDecimal >= $matchThreshold->toNative()) {
                return $this->baseMagentoProductRepository->getById($id);
            }
        }
    }
}
