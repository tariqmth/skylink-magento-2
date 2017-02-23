<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Customers;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupExtension;
use Magento\Customer\Api\Data\GroupExtensionFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Model\Customers\CustomerGroupExtensionAttributes;
use RetailExpress\SkyLink\Model\Customers\CustomerGroupPriceGroupHelper;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupKey as SkyLinkPriceGroupKey;

class GroupRepositoryPlugin
{
    use CustomerGroupExtensionAttributes;
    use CustomerGroupPriceGroupHelper;

    public function __construct(
        ResourceConnection $resourceConnection,
        GroupExtensionFactory $customerGroupExtensionFactory
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->customerGroupExtensionFactory = $customerGroupExtensionFactory;
    }

    public function afterGetById(GroupRepositoryInterface $subject, GroupInterface $magentoCustomerGroup)
    {
        /* @var SkyLinkPriceGroupKey|null $skyLinkPriceGroupKey */
        $skyLinkPriceGroupKey = $this->getSkyLinkPriceGroupKey($magentoCustomerGroup);

        if (null !== $skyLinkPriceGroupKey) {

            /* @var \Magento\Customer\Api\Data\GroupExtensionInterface $extendedAttributes */
            $extendedAttributes = $this->getCustomerGroupExtensionAttributes($magentoCustomerGroup);
            $extendedAttributes->setSkylinkPriceGroupKey($skyLinkPriceGroupKey);
        }

        return $magentoCustomerGroup;
    }

    /**
     * @todo switch this to an "after" method when possible, however the extended attibutes are wiped when doing it that way...
     */
    public function aroundSave(GroupRepositoryInterface $subject, callable $proceed, GroupInterface $magentoCustomerGroup)
    {
        /* @var \Magento\Customer\Api\Data\GroupExtensionInterface $extendedAttributes */
        $extendedAttributes = $this->getCustomerGroupExtensionAttributes($magentoCustomerGroup);

        /* @var SkyLinkPriceGroupKey|null $skyLinkPriceGroupKey */
        $skyLinkPriceGroupKey = $extendedAttributes->getSkyLinkPriceGroupKey();

        // Call our parent function
        $magentoCustomerGroup = $proceed($magentoCustomerGroup);

        if (null === $skyLinkPriceGroupKey) {
            return $magentoCustomerGroup;
        }

        $magentoCustomerGroupId = $magentoCustomerGroup->getId();

        // Update
        if (true === $this->mappingExists($magentoCustomerGroupId)) {
            $this->connection->update(
                $this->getCustomerGroupsPriceGroupsTable(),
                [
                    'skylink_price_group_type' => $skyLinkPriceGroupKey->getType(),
                    'skylink_price_group_id' => $skyLinkPriceGroupKey->getId(),
                ],
                [
                    'magento_customer_group_id = ?' => $magentoCustomerGroupId,
                ]
            );

        // Create
        } else {
            $this->connection->insert(
                $this->getCustomerGroupsPriceGroupsTable(),
                [
                    'skylink_price_group_type' => $skyLinkPriceGroupKey->getType(),
                    'skylink_price_group_id' => $skyLinkPriceGroupKey->getId(),
                    'magento_customer_group_id' => $magentoCustomerGroupId,
                ]
            );
        }

        return $magentoCustomerGroup;
    }

    /**
     * @param GroupInterface $magentoCustomerGroup
     *
     * @return SkyLinkPriceGroupKey|null
     */
    private function getSkyLinkPriceGroupKey(GroupInterface $magentoCustomerGroup)
    {
        $payload = $this->connection->fetchRow(
            $this->connection
                ->select()
                ->from($this->getCustomerGroupsPriceGroupsTable(), ['skylink_price_group_type', 'skylink_price_group_id'])
                ->where('magento_customer_group_id = ?', $magentoCustomerGroup->getId())
        );

        if (false === $payload) {
            return null;
        }

        return SkyLinkPriceGroupKey::fromNative($payload['skylink_price_group_type'], $payload['skylink_price_group_id']);
    }

    private function mappingExists($magentoCustomerGroupId)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getCustomerGroupsPriceGroupsTable())
                ->where('magento_customer_group_id = ?', $magentoCustomerGroupId)
        );
    }
}
