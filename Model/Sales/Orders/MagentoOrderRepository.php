<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

class MagentoOrderRepository implements MagentoOrderRepositoryInterface
{
    use OrderHelper;

    /**
     * Create a new Magento Order Repository.
     *
     * @param ResourceConnection       $resourceConnection
     * @param OrderRepositoryInterface $baseMagentoOrderRepository
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $baseMagentoOrderRepository
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->baseMagentoOrderRepository = $baseMagentoOrderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySkyLinkOrderId(SkyLinkOrderId $skyLinkOrderId)
    {
        $magentoOrderId = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getOrdersTable(), 'magento_order_id')
                ->where('skylink_order_id = ? ', $skyLinkOrderId)
        );

        if (false === $magentoOrderId) {
            return null;
        }

        return $this->baseMagentoOrderRepository->get($magentoOrderId);
    }
}
