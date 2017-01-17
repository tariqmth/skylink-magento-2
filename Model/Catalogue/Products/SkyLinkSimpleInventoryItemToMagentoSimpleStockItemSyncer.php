<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use RetailExpress\SkyLink\Api\ConfigInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSimpleProductStockItemMapperInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkInventoryItemToMagentoStockItemSyncerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepositoryFactory as SkyLinkProductRepositoryFactory;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class SkyLinkSimpleInventoryItemToMagentoSimpleStockItemSyncer implements SkyLinkInventoryItemToMagentoStockItemSyncerInterface
{
    const NAME = 'SkyLink Simple Inventory Item to Magento Simple Stock Item';

    private $config;

    private $skyLinkProductRepositoryFactory;

    private $magentoStockItemMapper;

    private $magentoStockRegistry;

    public function __construct(
        ConfigInterface $config,
        SkyLinkProductRepositoryFactory $skyLinkProductRepositoryFactory,
        MagentoSimpleProductStockItemMapperInterface $magentoStockItemMapper,
        ProductRepositoryInterface $baseMagentoProductRepository,
        StockRegistryInterface $magentoStockRegistry
    ) {
        $this->config = $config;
        $this->skyLinkProductRepositoryFactory = $skyLinkProductRepositoryFactory;
        $this->magentoStockItemMapper = $magentoStockItemMapper;
        $this->magentoStockRegistry = $magentoStockRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts(ProductInterface $magentoProduct)
    {
        return ProductType::TYPE_SIMPLE === $magentoProduct->getTypeId();
    }

    /**
     * {@inheritdoc}
     */
    public function sync(ProductInterface $magentoProduct)
    {
        /* @var \Magento\CatalogInventory\Api\Data\StockItemInterface $magentoStockItem */
        $magentoStockItem = $this->magentoStockRegistry->getStockItemBySku($magentoProduct->getSku());

        /* @var \Magento\Framework\Api\AttributeInterface|null $skyLinkProductIdAttribute */
        $skyLinkProductIdAttribute = $magentoProduct->getCustomAttribute('skylink_product_id');
        $skyLinkProductId = new SkyLinkProductId($skyLinkProductIdAttribute->getValue()); // @todo check here or in the command that calls this that we have one?

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepository $skyLinkProductRepository */
        $skyLinkProductRepository = $this->skyLinkProductRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\Product $skyLinkProduct */
        $skyLinkProduct = $skyLinkProductRepository->findSpecific(
            $skyLinkProductId,
            $this->config->getSalesChannelId()
        );

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\InventoryItem $skyLinkInventoryItem */
        $skyLinkInventoryItem = $skyLinkProduct->getInventoryItem();

        var_dump($skyLinkInventoryItem);

        // Map the stock item and save it
        $this->magentoStockItemMapper->mapStockItem($magentoStockItem, $skyLinkInventoryItem);
        $this->magentoStockRegistry->updateStockItemBySku($magentoProduct->getSku(), $magentoStockItem);
    }
}
