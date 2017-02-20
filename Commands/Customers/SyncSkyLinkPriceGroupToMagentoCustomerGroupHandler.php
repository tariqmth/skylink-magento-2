<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepositoryFactory;

class SyncSkyLinkPriceGroupToMagentoCustomerGroupHandler
{
    private $skyLinkPriceGroupRepositoryFactory;

    public function __construct(
        PriceGroupRepositoryFactory $skyLinkPriceGroupRepositoryFactory
    ) {
        $this->skyLinkPriceGroupRepositoryFactory = $skyLinkPriceGroupRepositoryFactory;
    }

    public function handle(SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand $command)
    {
        $skyLinkPriceGroupKey = SkyLinkPriceGroupKey::fromString($command->skyLinkPriceGroupKey);

        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepository $skyLinkPriceGroupRepository */
        $skyLinkPriceGroupRepository = $this->skyLinkPriceGroupRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroup $skyLinkPriceGroup */
        $skyLinkPriceGroup = $skyLinkPriceGroupRepository->find($skyLinkPriceGroupKey);

        // Find an existing customer group by the given price group and if none exists, create one (using the
        // SkyLink Price Group's name as the template for the Magento Customer Group).
    }
}
