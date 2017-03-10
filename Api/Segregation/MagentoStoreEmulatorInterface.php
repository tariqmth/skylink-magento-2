<?php

namespace RetailExpress\SkyLink\Api\Segregation;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;

interface MagentoStoreEmulatorInterface
{
    /**
     * Run the given callback with Magento scoped into the given Website.
     *
     * @param WebsiteInterface $magentoWebsite
     * @param callable         $callback
     *
     * @return mixed
     */
    public function onWebsite(WebsiteInterface $magentoWebsite, callable $callback);

    /**
     * Run the given callback with Magento scoped into the given Store.
     *
     * @param StoreInterface $magentoStore
     * @param callable       $callback
     *
     * @return mixed
     */
    public function onStore(StoreInterface $magentoStore, callable $callback);
}
