<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerGroupRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

class MagentoCustomerGroupRepository implements MagentoCustomerGroupRepositoryInterface
{
    use CustomerGroupPriceGroupHelper;

    private $baseMagentoCustomerGroupRepository;

    public function __construct(
        ResourceConnection $resourceConnection,
        GroupRepositoryInterface $baseMagentoCustomerGroupRepository
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->baseMagentoCustomerGroupRepository = $baseMagentoCustomerGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfMappedPriceGroupKeys()
    {
        $results = $this->connection->fetchAll(
            $this->connection
                ->select()
                ->from($this->getCustomerGroupsPriceGroupsTable())
        );

        return array_map(function (array $payload) {
            return SkyLinkPriceGroupKey::fromNative($payload['skylink_price_group_type'], $payload['skylink_price_group_id']);
        }, $results);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySkyLinkPriceGroupKey(SkyLinkPriceGroupKey $skyLinkPriceGroupKey)
    {
        $magentoCustomerGroupId = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getCustomerGroupsPriceGroupsTable())
                ->where('skylink_price_group_type = ?', $skyLinkPriceGroupKey->getType())
                ->where('skylink_price_group_id = ?', $skyLinkPriceGroupKey->getId())
        );

        if (false === $magentoCustomerGroupId) {
            return null;
        }

        return $this->baseMagentoCustomerGroupRepository->getById($magentoCustomerGroupId);
    }
}
