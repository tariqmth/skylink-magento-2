<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Products\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Exceptions\Products\TooManyProductMatchesException;

class MagentoSimpleProductRepository implements MagentoSimpleProductRepositoryInterface
{
    private $productConfig;

    private $baseMagentoProductRepository;

    private $searchCriteriaBuilder;

    /**
     * Database connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    public function __construct(
        ConfigInterface $productConfig,
        ProductRepositoryInterface $baseMagentoProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->productConfig = $productConfig;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * {@inheritdoc}
     *
     * @todo Maybe not use the DB directly? This could be super slow with large DBs however and this is used in the web.
     */
    public function getListOfMappedSkyLinkProductIds()
    {
        $results = $this->connection->fetchAll(
            $this->connection
                ->select()
                ->from(['attribute_values' => $this->getAttributeValuesTable()])
                ->join(
                    ['attributes' => $this->getAttributesTable()],
                    'attribute_values.attribute_id = attributes.attribute_id'
                )
                ->where('attributes.attribute_code = ?', 'skylink_product_id')
        );

        return array_map(function (array $payload) {
            return new SkyLinkProductId($payload['value']);
        }, $results);
    }

    private function getAttributesTable()
    {
        return $this->connection->getTableName('eav_attribute');
    }

    private function getAttributeValuesTable()
    {
        return $this->connection->getTableName('catalog_product_entity_varchar');
    }

    /**
     * {@inheritdoc}
     */
    public function findBySkyLinkProductId(SkyLinkProductId $skyLinkProductId)
    {
        // Search products by the given SkyLink Product ID
        $this->searchCriteriaBuilder->addFilter(
            'type_id',
            $this->productConfig->getProductTypesForSimpleProductSync(),
            'in'
        );

        $this->searchCriteriaBuilder->addFilter('skylink_product_id', (string) $skyLinkProductId);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $existingProducts = $this->baseMagentoProductRepository->getList($searchCriteria);
        $existingProductMatches = $existingProducts->getTotalCount();

        if ($existingProductMatches > 1) {
            throw TooManyProductMatchesException::withSkyLinkProductId($skyLinkProductId, $existingProductMatches);
        }

        if ($existingProductMatches === 1) {
            return $this->baseMagentoProductRepository->getById(
                current($existingProducts->getItems())->getId(),
                false,
                null,
                true
            );
        }
    }
}
