<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\NoMagentoOrderForSkyLinkOrderIdException;
use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

class MagentoOrderRepository implements MagentoOrderRepositoryInterface
{
    use OrderHelper;
    use OrderExtensionAttributes;

    private $searchCriteriaBuilder;

    private $baseMagentoOrderRepository;

    /**
     * Create a new Magento Order Repository.
     *
     * @param ResourceConnection       $resourceConnection
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param OrderRepositoryInterface $baseMagentoOrderRepository
     * @param OrderExtensionFactory    $orderExtensionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $baseMagentoOrderRepository,
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->baseMagentoOrderRepository = $baseMagentoOrderRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    public function getListOfActiveWithSkyLinkOrderIds()
    {
        // Let's filter by active orders
        $this->searchCriteriaBuilder->addFilter('state', [
            Order::STATE_NEW,
            Order::STATE_PENDING_PAYMENT,
            Order::STATE_PROCESSING,
        ], 'in');

        /* @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /* @var \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResults */
        $searchResults = $this->baseMagentoOrderRepository->getList($searchCriteria);

        return array_filter($searchResults->getItems(), function (OrderInterface $magentoOrder) {
            return (bool) $this->getOrderExtensionAttributes($magentoOrder)->getSkyLinkOrderId();
        });
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
            throw NoMagentoOrderForSkyLinkOrderIdException::withSkyLinkOrderId($skyLinkOrderId);
        }

        return $this->baseMagentoOrderRepository->get($magentoOrderId);
    }
}
