<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Store\Api\Data\WebsiteInterface;

trait SkyLinkProductToMagentoProductSyncer
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
