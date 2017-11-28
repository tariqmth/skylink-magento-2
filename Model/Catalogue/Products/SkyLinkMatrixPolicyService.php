<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeTypeManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkMatrixPolicyServiceInterface;
use RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy as SkyLinkMatrixPolicy;
use RetailExpress\SkyLink\Exceptions\Products\UnsupportedAttributeTypeException;

class SkyLinkMatrixPolicyService implements SkyLinkMatrixPolicyServiceInterface
{
    use SkyLinkMatrixPolicyHelper;

    private $magentoAttributeRepository;

    private $magentoAttributeTypeManager;

    /**
     * Create a new Magento Attribute Service.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository,
        MagentoAttributeTypeManagerInterface $magentoAttributeTypeManager
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $this->magentoAttributeRepository = $magentoAttributeRepository;
        $this->magentoAttributeTypeManager = $magentoAttributeTypeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function mapSkyLinkMatrixPolicyForSkyLinkProductType(
        SkyLinkMatrixPolicy $skyLinkMatrixPolicy,
        SkyLinkAttributeOption $skyLinkProductType
    ) {
        if ($this->mappingExists($skyLinkProductType)) {
            $this->connection->update(
                $this->getMatrixPoliciesTable(),
                ['skylink_attribute_codes' => $this->serialiseMatrixPolicy($skyLinkMatrixPolicy)],
                ['skylink_product_type_id = ? ' => $skyLinkProductType->getId()]
            );
        } else {
            $this->connection->insert(
                $this->getMatrixPoliciesTable(),
                [
                    'skylink_attribute_codes' => $this->serialiseMatrixPolicy($skyLinkMatrixPolicy),
                    'skylink_product_type_id' => $skyLinkProductType->getId(),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function filterSkyLinkAttributeCodesForUseInSkyLinkMatrixPolicies(array $skyLinkAttributeCodes)
    {
        $skyLinkAttributeCodes = array_filter(
            $skyLinkAttributeCodes,
            function (SkyLinkAttributeCode $skylinkAttributeCode) {

                // Filter by allowed attributes
                if (false === SkyLinkMatrixPolicy::attributeCodeIsAllowed($skylinkAttributeCode)) {
                    return false;
                }

                /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface|null $magentoAttribute */
                $magentoAttribute = $this
                    ->magentoAttributeRepository
                    ->getMagentoAttributeForSkyLinkAttributeCode($skylinkAttributeCode);

                // Filter by mapped Magento Attributes
                if (null === $magentoAttribute) {
                    return false;
                }

                // Filter by configurable Magento Attributes
                try {
                    return $this
                        ->magentoAttributeTypeManager
                        ->getType($magentoAttribute)
                        ->sameValueAs(MagentoAttributeType::get('configurable'));
                } catch (UnsupportedAttributeTypeException $e) {
                    return false;
                }
            }
        );

        return array_values($skyLinkAttributeCodes);
    }

    private function serialiseMatrixPolicy(SkyLinkMatrixPolicy $skyLinkMatrixPolicy)
    {
        return implode(',', $skyLinkMatrixPolicy->getAttributeCodes());
    }

    private function mappingExists(SkyLinkAttributeOption $skyLinkProductType)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getMatrixPoliciesTable(), 'count(skylink_product_type_id)')
                ->where('skylink_product_type_id = ?', $skyLinkProductType->getId())
        );
    }
}
