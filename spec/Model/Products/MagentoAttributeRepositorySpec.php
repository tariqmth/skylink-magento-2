<?php

namespace spec\RetailExpress\SkyLink\Model\Products;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface as ConnectionAdaptorInterface;
use Magento\Framework\DB\Select as DbSelect;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Model\Products\MagentoAttributeRepository;

class MagentoAttributeRepositorySpec extends ObjectBehavior
{
    private $resourceConnection;

    private $connection;

    private $magentoProductAttributeRepository;

    public function let(
        ResourceConnection $resourceConnection,
        ConnectionAdaptorInterface $connection,
        ProductAttributeRepositoryInterface $magentoProductAttributeRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $connection;
        $this->magentoProductAttributeRepository = $magentoProductAttributeRepository;

        $this->beConstructedWith($this->resourceConnection, $this->magentoProductAttributeRepository);

        $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION)->willReturn($this->connection);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoAttributeRepository::class);
    }

    public function it_returns_null_when_the_database_query_returns_false(
        DbSelect $dbSelect
    ) {
        $skyLinkAttributeCode = SkyLinkAttributeCode::get('brand');

        $this->connection
            ->getTableName('retail_express_skylink_attributes')
            ->shouldBeCalled()
            ->willReturn('custom_attributes_table');

        $this->connection
            ->select()
            ->shouldBeCalled()
            ->willReturn($dbSelect);

        $dbSelect
            ->from('custom_attributes_table', 'magento_attribute_code')
            ->shouldBeCalled()
            ->willReturn($dbSelect);

        $dbSelect
            ->where('skylink_attribute_code = ?', 'brand')
            ->shouldBeCalled()
            ->willReturn($dbSelect);

        $this->connection->fetchOne($dbSelect)->shouldBeCalled()->willReturn(false);

        $this->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode)->shouldBeNull();
    }

    public function it_queries_the_magento_product_attribute_repository_when_a_magento_attribute_mapping_is_found(
        DbSelect $dbSelect,
        ProductAttributeInterface $productAttribute
    ) {
        $skyLinkAttributeCode = SkyLinkAttributeCode::get('colour');

        $this->connection
            ->getTableName('retail_express_skylink_attributes')
            ->shouldBeCalled()
            ->willReturn('retail_express_skylink_attributes');

        $this->connection
            ->select()
            ->shouldBeCalled()
            ->willReturn($dbSelect);

        $dbSelect
            ->from('retail_express_skylink_attributes', 'magento_attribute_code')
            ->shouldBeCalled()
            ->willReturn($dbSelect);

        $dbSelect
            ->where('skylink_attribute_code = ?', 'colour')
            ->shouldBeCalled()
            ->willReturn($dbSelect);

        $this->connection->fetchOne($dbSelect)->shouldBeCalled()->willReturn('my_magento_attribute_code');

        $this->magentoProductAttributeRepository
            ->get('my_magento_attribute_code')
            ->shouldBeCalled()
            ->willReturn($productAttribute);

        $this->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode)->shouldReturn($productAttribute);
    }
}
