<?php

namespace RetailExpress\SkyLink\Api\Products;

interface ConfigInterface
{
    /**
     * Returns the threshold used to match configurable products.
     *
     * @return \RetailExpress\SkyLink\ValueObjects\ConfigurableProductMatchThreshold
     */
    public function getConfigurableProductMatchThreshold();
}
