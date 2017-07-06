<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

interface ConfigInterface
{
    /**
     * Returns the name attribute used for mapping product names.
     *
     * @return \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductNameAttribute
     */
    public function getNameAttribute();

    /**
     * Returns the sync strategy used for mapping product names.
     *
     * @return \RetailExpress\SkyLink\Model\Catalogue\Products\SyncStrategy
     */
    public function getNameSyncStrategy();

    /**
     * Returns the name attribute used for mapping regular prices.
     *
     * @return \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductPriceAttribute
     */
    public function getRegularPriceAttribute();

    /**
     * Returns the name attribute used for mapping special prices.
     *
     * @return \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductPriceAttribute
     */
    public function getSpecialPriceAttribute();

    /**
     * Get the quantity calculation for a product.
     *
     * @return \RetailExpress\SkyLink\Model\Catalogue\Products\QuantityCalculation
     */
    public function getQuantityCalculation();

    /**
     * Returns the URL Key Attribute Codes.
     *
     * @return string[]
     */
    public function getUrlKeyAttributeCodes();

    /**
     * Returns the time (in seconds) that composite products can have their sync re-ran.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getCompositeProductSyncRerunThreshold();
}
