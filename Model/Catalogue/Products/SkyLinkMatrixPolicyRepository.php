<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkMatrixPolicyRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy;

class SkyLinkMatrixPolicyRepository implements SkyLinkMatrixPolicyRepositoryInterface
{
    use SkyLinkMatrixPolicyHelper;

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
     * {@inheritdoc}
     */
    public function getMatrixPolicyForProductType(SkyLinkAttributeOption $skyLinkProductType)
    {
        $skyLinkAttributeCodes = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getMatrixPoliciesTable(), 'skylink_attribute_codes')
                ->where('skylink_product_type_id = ?', $skyLinkProductType->getId())
        );

        if (false === $skyLinkAttributeCodes) {
            return MatrixPolicy::getDefault();
        }

        return MatrixPolicy::fromNative($this->deserialiseAttributeCodes($skyLinkAttributeCodes));
    }

    private function deserialiseAttributeCodes($skyLinkAttributeCodes)
    {
        return array_map('trim', explode(',', $skyLinkAttributeCodes));
    }
}
