<?php

namespace RetailExpress\SkyLink\Model\Products;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Products\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

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
     * Create a new Magento Attribute Repository.
     *
     * @param ResourceConnection                  $resourceConnection
     * @param ProductAttributeRepositoryInterface $magentoProductAttributeRepository
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductAttributeRepositoryInterface $magentoProductAttributeRepository
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->magentoProductAttributeRepository = $magentoProductAttributeRepository;
    }

    /**
     * Get the Attribute used for the given SkyLink Attribute Code. If there is no
     * mapping defined, "null" is returend.
     *
     * @param SkyLinkAttributeCode $skylinkAttributeCode
     *
     * @return AttributeInterface|null
     */
    public function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode)
    {
        $magentoAttributeCode = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getAttributesTable(), 'magento_attribute_code')
                ->where('skylink_attribute_code = ?', $skylinkAttributeCode->getValue())
        );

        if (false === $magentoAttributeCode) {
            return null;
        }

        return $this->magentoProductAttributeRepository->get($magentoAttributeCode);
    }
}
