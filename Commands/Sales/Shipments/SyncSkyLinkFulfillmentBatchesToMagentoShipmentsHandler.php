<?php

namespace RetailExpress\SkyLink\Commands\Sales\Shipments;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoShipmentServiceInterface;
use RetailExpress\SkyLink\Api\Sales\Shipments\MagentoShipmentRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Sales\Fulfillments\Batch as SkyLinkFulfillmentBatch;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepositoryFactory as SkyLinkOrderRepositoryFactory;

class SyncSkyLinkFulfillmentBatchesToMagentoShipmentsHandler
{
    private $config;

    private $skyLinkOrderRepositoryFactory;

    private $magentoShipmentRepository;

    private $magentoShipmentService;

    public function __construct(
        ConfigInterface $config,
        SkyLinkOrderRepositoryFactory $skyLinkOrderRepositoryFactory,
        MagentoShipmentRepositoryInterface $magentoShipmentRepository,
        MagentoShipmentServiceInterface $magentoShipmentService
    ) {
        $this->config = $config;
        $this->skyLinkOrderRepositoryFactory = $skyLinkOrderRepositoryFactory;
        $this->magentoShipmentRepository = $magentoShipmentRepository;
        $this->magentoShipmentService = $magentoShipmentService;
    }

    public function handle(SyncSkyLinkFulfillmentBatchesToMagentoShipmentsCommand $command)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepository $skyLinkOrderRepository */
        $skyLinkOrderRepository = $this->skyLinkOrderRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\Order $skyLinkOrder */
        $skyLinkOrder = $skyLinkOrderRepository->get(
            $this->config->getSalesChannelId(),
            new SkyLinkOrderId($command->skyLinkOrderId)
        );

        // Iterate over the fulfillment batches for the SkyLink Order and try find our
        // own corresponding shipments. If we don't find one, we'll create it instead.
        array_map(
            function (SkyLinkFulfillmentBatch $skyLinkFulfillmentBatch) use ($skyLinkOrder) {

                /* @var \Magento\Sales\Api\Data\ShipmentInterface|null $magentoShipment */
                $magentoShipment = $this->magentoShipmentRepository->findBySkyLinkFulfillmentBatchId(
                    $skyLinkFulfillmentBatch->getId()
                );

                if (null !== $magentoShipment) {
                    return;
                }

                $this
                    ->magentoShipmentService
                    ->createMagentoShipmentFromSkyLinkFulfillmentBatch($skyLinkOrder, $skyLinkFulfillmentBatch);
            },
            $skyLinkOrder->getFulfillmentBatches()
        );
    }
}
