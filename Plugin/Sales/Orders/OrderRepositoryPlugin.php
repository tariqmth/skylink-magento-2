<?php

namespace RetailExpress\SkyLink\Plugin\Sales\Orders;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Sales\Orders\CreateSkyLinkOrderFromMagentoOrderCommand;
use RetailExpress\SkyLink\Model\Sales\Orders\OrderExtensionAttributes;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

class OrderRepositoryPlugin
{
    use OrderExtensionAttributes;

    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * The command bus.
     *
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * Create a new Order Repository Plugin.
     *
     * @param ResourceConnection    $resourceConnection
     * @param CommandBusInterface   $commandBus
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CommandBusInterface $commandBus,
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->commandBus = $commandBus;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $magentoOrder)
    {
        $skyLinkOrderIdString = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getOrdersTable(), 'skylink_order_id')
                ->where('magento_order_id = ?', $magentoOrder->getEntityId())
        );

        if (false !== $skyLinkOrderIdString) {
            /* @var \Magento\Sales\Api\Data\OrderExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->getOrderExtensionAttributes($magentoOrder);
            $extendedAttributes->setSkyLinkOrderId(new SkyLinkOrderId($skyLinkOrderIdString));
        }

        return $magentoOrder;
    }

    public function aroundSave(OrderRepositoryInterface $subject, callable $proceed, OrderInterface $magentoOrder)
    {
        // Send new orders to Magento
        $isNew = !$magentoOrder->getEntityId();

        // Call the original repository
        $magentoOrder = $proceed($magentoOrder);

        // Grab the Magento Order ID, we'll use this a big
        $magentoOrderId = $magentoOrder->getEntityId();

        // Send new order to Retail Express
        if (true === $isNew) {
            $command = new CreateSkyLinkOrderFromMagentoOrderCommand();
            $command->magentoOrderId = $magentoOrderId;
            $this->commandBus->handle($command);

            return $magentoOrder;
        }

        /* @var \Magento\Sales\Api\Data\OrderExtensionInterface $extendedAttributes */
        $extendedAttributes = $this->getOrderExtensionAttributes($magentoOrder);
        $skyLinkOrderId = $extendedAttributes->getSkyLinkOrderId();

        // When we send the order to Retail Express, the handler should save the
        // Retail Express Order ID for the given order. At that point, we're
        // back to this method. If that is the case, we'll add a mapping
        // into our tables if one is now on the order and not persisted.
        if (null !== $skyLinkOrderId && !$this->mappingExists($magentoOrderId, $skyLinkOrderId)) {
            $this->addMapping($magentoOrderId, $skyLinkOrderId);
        }

        return $magentoOrder;
    }

    private function mappingExists($magentoOrderId, SkyLinkOrderId $skyLinkOrderId)
    {
         return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getOrdersTable(), 'count(skylink_order_id)')
                ->where('magento_order_id = ?', $magentoOrderId)
                ->where('skylink_order_id = ?', $skyLinkOrderId) // @todo Is this needed with our database primary keys?
        );
    }

    private function addMapping($magentoOrderId, SkyLinkOrderId $skyLinkOrderId)
    {
        // Insert a mapping in our database
        $this->connection->insert(
            $this->getOrdersTable(),
            [
                'magento_order_id' => $magentoOrderId,
                'skylink_order_id' => $skyLinkOrderId,
            ]
        );
    }

    private function getOrdersTable()
    {
        return $this->connection->getTableName('retail_express_skylink_orders');
    }
}
