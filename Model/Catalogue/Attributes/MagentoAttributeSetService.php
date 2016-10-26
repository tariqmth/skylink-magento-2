<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MagentoAttributeSetService implements MagentoAttributeSetServiceInterface
{
    use MagentoAttributeSet;

    /**
     * Create a new Magento Attribute Service.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * Defines the Attribute Set used when SkyLink creates a new product in
     * Magento for the given SkyLink "product type".
     *
     * @param AttributeSetInterface  $magentoAttributeSet
     * @param SkyLinkAttributeOption $skyLinkProductType
     */
    public function mapAttributeSetForProductType(
        AttributeSetInterface $magentoAttributeSet,
        SkyLinkAttributeOption $skyLinkProductType
    ) {
        if ($this->mappingExists($skyLinkProductType)) {
            $this->connection->update(
                $this->getAttributeSetsTable(),
                ['magento_attribute_set_id' => $magentoAttributeSet->getAttributeSetId()],
                ['skylink_product_type_id = ? ' => $skyLinkProductType->getId()]
            );
        } else {
            $this->connection->insert(
                $this->getAttributeSetsTable(),
                [
                    'magento_attribute_set_id' => $magentoAttributeSet->getAttributeSetId(),
                    'skylink_product_type_id' => $skyLinkProductType->getId(),
                ]
            );
        }
    }

    private function mappingExists(SkyLinkAttributeOption $skyLinkProductType)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getAttributeSetsTable(), 'count(skylink_product_type_id)')
                ->where('skylink_product_type_id = ?', $skyLinkProductType->getId())
        );
    }
}
