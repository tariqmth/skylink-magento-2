<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class MagentoAttributeRepository implements MagentoAttributeRepositoryInterface
{
    use MagentoAttribute;

    /**
     * The Magento Product Attribute Repository, used for fetching attributes
     * based on their attribute code stored by a mapping.
     *
     * @var ProductAttributeRepositoryInterface
     */
    private $magentoProductAttributeRepository;

    /**
     * Chosen Attribute Mappings.
     *
     * @var array
     */
    private $attributeMappings;

    /**
     * Return an array of attribute mapping overrides whereby we use a different
     * attribute code within Magento to represent a SkyLink Attribute Code.
     *
     * @return array
     */
    private static function getDefaultAttributeMappingOverrides()
    {
        return [
            'brand' => 'manufacturer',
            'colour' => 'color',
        ];
    }

    /**
     * Create a new Magento Attribute Repository.
     *
     * @param ResourceConnection                  $resourceConnection
     * @param ProductAttributeRepositoryInterface $magentoProductAttributeRepository
     * @param array|null                          $attributeMappings
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductAttributeRepositoryInterface $magentoProductAttributeRepository,
        array $attributeMappings = null
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->magentoProductAttributeRepository = $magentoProductAttributeRepository;

        if (null === $attributeMappings) {
            $attributeMappings = $this->getDefaultAttributeMappings();
        }

        $this->attributeMappings = $attributeMappings;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode)
    {
        $magentoAttributeCode = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getAttributesTable(), 'magento_attribute_code')
                ->where('skylink_attribute_code = ?', $skylinkAttributeCode->getValue()) // @todo can we remove getValue()?
        );

        if (false === $magentoAttributeCode) {
            return null;
        }

        return $this->magentoProductAttributeRepository->get($magentoAttributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode)
    {
        $magentoAttributeCode = $this->attributeMappings[$skylinkAttributeCode->getValue()];

        return $this->magentoProductAttributeRepository->get($magentoAttributeCode);
    }

    /**
     * Get the default attribute mappings by merging in all valid SkyLink Attribute Codes with
     * predetermined mapping overrides.
     *
     * @return array Key => Value of SkyLink Attribute Code => Magento Attribute Code
     */
    private function getDefaultAttributeMappings()
    {
        $skylinkAttributeCodeStrings = SkyLinkAttributeCode::getConstants();

        // Set keys / values to be the same
        $defaultMappings = array_combine($skylinkAttributeCodeStrings, $skylinkAttributeCodeStrings);

        // Override with mapping overrides
        $defaultMappings = array_merge($defaultMappings, self::getDefaultAttributeMappingOverrides());

        return $defaultMappings;
    }
}
