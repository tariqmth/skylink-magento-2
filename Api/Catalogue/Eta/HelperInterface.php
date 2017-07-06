<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Eta;

use Magento\Catalog\Api\Data\ProductInterface;

interface HelperInterface
{
    /**
     * Determine if ETA can show for the given product or not.
     *
     * @param ProductInterface $magentoProduct
     *
     * @return bool
     */
    public function canShow(ProductInterface $magentoProduct);
}
