<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Customers;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupRegistry;

/**
 * @see \RetailExpress\SkyLink\Plugin\SkyLink\Customers\SyncMagentoCustomerToSkyLinkCustomerCacheBusterPlugin
 */
class GroupRepositoryCacheBusterPlugin
{
    private $magentoCustomerGroupRegistry;

    public function __construct(GroupRegistry $magentoCustomerGroupRegistry)
    {
        $this->magentoCustomerGroupRegistry = $magentoCustomerGroupRegistry;
    }

    /**
     * Busts registry cache before fetching price groups.
     *
     * @todo Make this only occur during syncs?
     */
    public function beforeGetById(GroupRepositoryInterface $subject, $magentoGroupId)
    {
        $this->magentoCustomerGroupRegistry->remove($magentoGroupId);

        return [$magentoGroupId];
    }
}
