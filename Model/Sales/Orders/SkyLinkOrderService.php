<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelIdRepositoryInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderServiceInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepositoryFactory;

class SkyLinkOrderService implements SkyLinkOrderServiceInterface
{
    use OrderExtensionAttributes;

    private $skyLinkOrderRepositoryFactory;

    private $salesChannelIdRepository;

    public function __construct(
        OrderRepositoryFactory $skyLinkOrderRepositoryFactory,
        SalesChannelIdRepositoryInterface $salesChannelIdRepository,
        OrderExtensionFactory $orderExtensionFactory,
        OrderRepositoryInterface $baseMagentoOrderRepository
    ) {
        $this->skyLinkOrderRepositoryFactory = $skyLinkOrderRepositoryFactory;
        $this->salesChannelIdRepository = $salesChannelIdRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->baseMagentoOrderRepository = $baseMagentoOrderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function placeSkyLinkOrder(SkyLinkOrder $skyLinkOrder, OrderInterface $magentoOrder)
    {
        /* @var \RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepository $skyLinkOrderRepository */
        $skyLinkOrderRepository = $this->skyLinkOrderRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId $salesChannelId */
        $salesChannelId = $this->salesChannelIdRepository->getSalesChannelIdForCurrentWebsite();

        // Add to SkyLink
        $skyLinkOrderRepository->add($salesChannelId, $skyLinkOrder);

        // Now we'll grab the extension attributes instance and set the SkyLink Order ID
        $extendedAttributes = $this->getOrderExtensionAttributes($magentoOrder);
        $extendedAttributes->setSkylinkOrderId($skyLinkOrder->getId()); // @todo check for existing SkyLink Order ID? Note lowercsae "l"
        $extendedAttributes->setSalesChannelId($salesChannelId);

        // Save the Magento Order
        $this->baseMagentoOrderRepository->save($magentoOrder);
    }
}
