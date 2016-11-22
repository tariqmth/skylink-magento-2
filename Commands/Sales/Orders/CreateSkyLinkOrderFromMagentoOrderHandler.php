<?php

namespace RetailExpress\SkyLink\Commands\Sales\Orders;

use Magento\Sales\Api\OrderRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderServiceInterface;

class CreateSkyLinkOrderFromMagentoOrderHandler
{
    private $baseMagentoOrderRepository;

    private $skyLinkOrderBuilder;

    private $skyLinkOrderService;

    public function __construct(
        OrderRepositoryInterface $baseMagentoOrderRepository,
        SkyLinkOrderBuilderInterface $skyLinkOrderBuilder,
        SkyLinkOrderServiceInterface $skyLinkOrderService
    ) {
        $this->baseMagentoOrderRepository = $baseMagentoOrderRepository;
        $this->skyLinkOrderBuilder = $skyLinkOrderBuilder;
        $this->skyLinkOrderService = $skyLinkOrderService;
    }

    public function handle(CreateSkyLinkOrderFromMagentoOrderCommand $command)
    {
        /* @var \Magento\Sales\Api\Data\OrderInterface $magentoOrder */
        $magentoOrder = $this->baseMagentoOrderRepository->get($command->magentoOrderId);

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\Order $skyLinkOrder */
        $skyLinkOrder = $this->skyLinkOrderBuilder->buildFromMagentoOrder($magentoOrder);

        // Place the SkyLink Order
        $this->skyLinkOrderService->placeSkyLinkOrder($skyLinkOrder, $magentoOrder);
    }
}
