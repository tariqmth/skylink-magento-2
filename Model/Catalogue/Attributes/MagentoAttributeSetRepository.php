<?php

namespace RetailExpress\SkyLink\Model\Products;

use Magento\Catalog\Api\AttributeSetRepositoryInterface as BaseAttributeSetRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MagentoAttributeSetRepository implements MagentoAttributeSetRepositoryInterface
{
    use MagentoAttributeSet;

    private $baseMagentoAttributeSetRepository;

    /**
     * Create a new Magento Attribute Service.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        BaseAttributeSetRepositoryInterface $baseMagentoAttributeSetRepository
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $this->baseMagentoAttributeSetRepository = $baseMagentoAttributeSetRepository;
    }

    /**
     * Get the Attribute Set used for the given product type. If there is no
     * mapping defined, "null" is returend.
     *
     * @param SkyLinkAttributeOption $skyLinkProductType
     *
     * @return \Magento\Eav\Api\Data\AttributeSetInterface|null
     */
    public function getAttributeSetForProductType(SkyLinkAttributeOption $skyLinkProductType)
    {
        $magentoAttributeSetId = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getAttributeSetsTable(), 'magento_attribute_set_id')
                ->where('skylink_product_type_id = ?', $skyLinkProductType->getId())
        );

        if (false === $magentoAttributeSetId) {
            return null;
        }

        return $this->baseMagentoAttributeSetRepository->get($magentoAttributeSetId);
    }
}
