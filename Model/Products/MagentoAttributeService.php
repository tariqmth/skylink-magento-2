<?php

namespace RetailExpress\SkyLink\Model\Products;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Api\Products\MagentoAttributeServiceInterface;

class MagentoAttributeService
{
    use MagentoAttribute;

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
     * Defines the Attribute used when SkyLink synchronises a Product.
     *
     * @param AttributeInterface   $magentoAttribute
     * @param SkyLinkAttributeCode $skylinkAttributeCode
     */
    public function mapAttributeForProductType(
        AttributeInterface $magentoAttribute,
        SkyLinkAttributeCode $skylinkAttributeCode
    ) {
        if ($this->mappingExists($skylinkAttributeCode)) {
            $this->connection->update(
                $this->getAttributesTable(),
                ['magento_attribute' => $magentoAttribute->getAttributeCode()],
                ['skylink_attribute_code = ? ', $skylinkAttributeCode->getValue()]
            );
        } else {
            $this->connection->insert(
                $this->getAttributesTable(),
                [
                    'magento_attribute' => $magentoAttribute->getAttributeCode(),
                    'skylink_attribute_code' => $skylinkAttributeCode->getValue(),
                ]
            );
        }
    }

    private function mappingExists(SkyLinkAttributeCode $skylinkAttributeCode)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getAttributesTable(), 'count(skylink_attribute_code)')
                ->where('skylink_attribute_code = ?', $skylinkAttributeCode->getValue())
        );
    }
}
