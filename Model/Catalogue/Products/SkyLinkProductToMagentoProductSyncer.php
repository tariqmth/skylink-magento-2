<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Framework\Exception\NoSuchEntityException;

trait SkyLinkProductToMagentoProductSyncer
{
    /**
     * @var ProductRepositoryInterface
     */
    private $baseMagentoProductRepository;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Attempts to find an existing Magento product with the given sku.
     *
     * @return \Magento\Catalog\Api\ProductRepositoryInterface|null
     */
    public function getMagentoProduct($sku)
    {
        try {
            return $this->baseMagentoProductRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            //
        }
    }
}
