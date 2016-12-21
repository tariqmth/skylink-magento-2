<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

interface ConfigInterface
{
    /**
     * Returns the threshold used to match configurable products.
     *
     * @return \RetailExpress\SkyLink\ValueObjects\ConfigurableProductMatchThreshold
     */
    public function getConfigurableProductMatchThreshold();

    /**
     * Returns the time (in seconds) that composite products can have their sync re-ran.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getCompositeProductSyncRerunThreshold();
}
