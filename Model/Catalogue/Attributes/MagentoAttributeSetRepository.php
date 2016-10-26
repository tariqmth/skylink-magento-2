<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Catalog\Api\AttributeSetRepositoryInterface as BaseAttributeSetRepositoryInterface;
use Magento\Catalog\Model\ProductFactory as MagentoProductFactory;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MagentoAttributeSetRepository implements MagentoAttributeSetRepositoryInterface
{
    use MagentoAttributeSet;

    private $baseMagentoAttributeSetRepository;

    private $magentoProductFactory;

    /**
     * Create a new Magento Attribute Service.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        BaseAttributeSetRepositoryInterface $baseMagentoAttributeSetRepository,
        MagentoProductFactory $magentoProductFactory
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $this->baseMagentoAttributeSetRepository = $baseMagentoAttributeSetRepository;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    /**
     * {@inheritdoc}
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

            /** @var \Magento\Catalog\Model\Product $magentoProduct **/
            $magentoProduct = $this->magentoProductFactory->create();
            $magentoAttributeSetId = $magentoProduct->getDefaultAttributeSetId();
        }

        return $this->baseMagentoAttributeSetRepository->get($magentoAttributeSetId);
    }
}
