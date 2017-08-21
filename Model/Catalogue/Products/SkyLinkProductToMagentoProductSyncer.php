<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

trait SkyLinkProductToMagentoProductSyncer
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoProduct($sku)
    {
        try {
            return $this->baseMagentoProductRepository->get($sku);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            return false;
        }
    }
}
