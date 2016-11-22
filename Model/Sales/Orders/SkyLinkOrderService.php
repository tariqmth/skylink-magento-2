<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkOrderServiceInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\Order as SkyLinkOrder;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderRepositoryFactory;

class SkyLinkOrderService implements SkyLinkOrderServiceInterface
{
    private $skyLinkOrderRepositoryFactory;

    private $config;

    private $orderExtensionFactory;

    public function __construct(
        OrderRepositoryFactory $skyLinkOrderRepositoryFactory,
        ConfigInterface $config,
        OrderExtensionFactory $orderExtensionFactory,
        OrderRepositoryInterface $baseMagentoOrderRepository
    ) {
        $this->skyLinkOrderRepositoryFactory = $skyLinkOrderRepositoryFactory;
        $this->config = $config;
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

        // Add to SkyLink
        $skyLinkOrderRepository->add(
            $this->config->getSalesChannelId(),
            $skyLinkOrder
        );

        // Now we'll grab the extension attributes instance and set the SkyLink Order ID
        $extendedAttributes = $this->getOrderExtensionAttributes($magentoOrder);
        $extendedAttributes->setSkylinkOrderId($skyLinkOrder->getId()); // @todo check for existing SkyLink Order ID?

        // Save the Magento Order
        $this->baseMagentoOrderRepository->save($magentoOrder);
    }

    private function getOrderExtensionAttributes(OrderInterface $magentoOrder)
    {
        $extendedAttributes = $magentoOrder->getExtensionAttributes();

        if (null === $extendedAttributes) {

            /* @var OrderExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->orderExtensionFactory->create();
            $magentoOrder->setExtensionAttributes($extendedAttributes);
        }

        return $extendedAttributes;
    }
}
