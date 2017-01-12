<?php

namespace RetailExpress\SkyLink\Commands\Sales\Orders;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderBuilderInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderServiceInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\SkyLinkOrderAlreadyPlacedForMagentoOrderException;
use RetailExpress\SkyLink\Model\Sales\Orders\OrderExtensionAttributes;

class CreateSkyLinkOrderFromMagentoOrderHandler
{
    use OrderExtensionAttributes;

    private $baseMagentoOrderRepository;

    private $skyLinkOrderBuilder;

    private $skyLinkOrderService;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    public function __construct(
        OrderRepositoryInterface $baseMagentoOrderRepository,
        OrderExtensionFactory $orderExtensionFactory,
        SkyLinkOrderBuilderInterface $skyLinkOrderBuilder,
        SkyLinkOrderServiceInterface $skyLinkOrderService,
        SkyLinkLoggerInterface $logger
    ) {
        $this->baseMagentoOrderRepository = $baseMagentoOrderRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->skyLinkOrderBuilder = $skyLinkOrderBuilder;
        $this->skyLinkOrderService = $skyLinkOrderService;
        $this->logger = $logger;
    }

    public function handle(CreateSkyLinkOrderFromMagentoOrderCommand $command)
    {
        /* @var \Magento\Sales\Api\Data\OrderInterface $magentoOrder */
        $magentoOrder = $this->baseMagentoOrderRepository->get($command->magentoOrderId);

        $extendedAttributes = $this->getOrderExtensionAttributes($magentoOrder);
        $skyLinkorderId = $extendedAttributes->getSkyLinkOrderId();

        // The Magento Order can't already be associated with a SkyLink Order
        if (null !== $skyLinkorderId) {
            $e = SkyLinkOrderAlreadyPlacedForMagentoOrderException::withMagentoOrderAndSkyLinkOrderId(
                $magentoOrder,
                $skyLinkorderId
            );

            $this->logger->error('Attempting to place a SkyLink Order twice from the same Magento Order.', [
                'Error' => $e->getMessage(),
                'Magento Order ID' => $magentoOrder->getIncrementId(),
                'SkyLink Order ID' => $skyLinkorderId,
            ]);

            throw $e;
        }

        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\Order $skyLinkOrder */
        $skyLinkOrder = $this->skyLinkOrderBuilder->buildFromMagentoOrder($magentoOrder);

        // Place the SkyLink Order
        $this->skyLinkOrderService->placeSkyLinkOrder($skyLinkOrder, $magentoOrder);
    }
}
