<?php

namespace RetailExpress\SkyLink\Commands\Customers;

use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepositoryFactory;

class SyncSkyLinkPriceGroupToMagentoCustomerGroupHandler
{
    private $skyLinkPriceGroupRepositoryFactory;

    private $magentoCustomerGroupRepository;

    private $magentoCustomerGroupService;

    public function __construct(
        PriceGroupRepositoryFactory $skyLinkPriceGroupRepositoryFactory,
        MagentoCustomerGroupRepositoryInterface $magentoCustomerGroupRepository,
        MagentoCustomerGroupServiceInterface $magentoCustomerGroupService
    ) {
        $this->skyLinkPriceGroupRepositoryFactory = $skyLinkPriceGroupRepositoryFactory;
        $this->magentoCustomerGroupRepository = $magentoCustomerGroupRepository;
        $this->magentoCustomerGroupService = $magentoCustomerGroupService;
    }

    public function handle(SyncSkyLinkPriceGroupToMagentoCustomerGroupCommand $command)
    {
        $skyLinkPriceGroupKey = SkyLinkPriceGroupKey::fromString($command->skyLinkPriceGroupKey);

        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupRepository $skyLinkPriceGroupRepository */
        $skyLinkPriceGroupRepository = $this->skyLinkPriceGroupRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroup $skyLinkPriceGroup */
        $skyLinkPriceGroup = $skyLinkPriceGroupRepository->find($skyLinkPriceGroupKey);

        /* @var \Magento\Customer\Api\Data\GroupInterface|null $magentoCustomerGroup */
        $magentoCustomerGroup = $this->magentoCustomerGroupRepository->findBySkyLinkPriceGroupKey($skyLinkPriceGroupKey);


        if (null === $magentoCustomerGroup) {
            $magentoCustomerGroup = $this->magentoCustomerGroupService->createMagentoCustomerGroup($skyLinkPriceGroup);
        }

        // @todo add logging and event triggers
    }
}
