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

    private function assertMagentoWebsites(array $magentoWebsites)
    {
        array_walk($magentoWebsites, function (WebsiteInterface $magentoWebsite) {
            //
        });
    }
}
