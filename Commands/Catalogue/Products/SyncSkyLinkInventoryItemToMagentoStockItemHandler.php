<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use Magento\Catalog\Api\ProductRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkInventoryItemToMagentoStockItemSyncerInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;

class SyncSkyLinkInventoryItemToMagentoStockItemHandler
{
    private $baseMagentoProductRepository;

    private $syncers = [];

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    public function __construct(
        ProductRepositoryInterface $baseMagentoProductRepository,
        array $syncers,
        SkyLinkLoggerInterface $logger
    ) {
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;

        array_walk($syncers, function (SkyLinkInventoryItemToMagentoStockItemSyncerInterface $syncer) {
            $this->syncers[] = $syncer;
        });

        $this->logger = $logger;
    }

    /**
     * @param SyncSkyLinkInventoryItemToMagentoStockItemCommand $command
     */
    public function handle(SyncSkyLinkInventoryItemToMagentoStockItemCommand $command)
    {
        /* @var \Magento\Catalog\Api\Data\ProductInterface $magentoProduct */
        $magentoProduct = $this->baseMagentoProductRepository->getById(
            $command->magentoProductId,
            false,
            null,
            true
        );

        /* @var \Magento\Framework\Api\AttributeInterface|null $skyLinkProductIdAttribute */
        $skyLinkProductIdAttribute = $magentoProduct->getCustomAttribute('skylink_product_id');

        if (null === $skyLinkProductIdAttribute) {
            // @todo add exception!
        }

        foreach ($this->syncers as $syncer) {
            if (!$syncer->accepts($magentoProduct)) {
                continue;
            }

            $this->logger->info('Syncing SkyLink Inventory Item to Magento Stock Item', [
                'Magento Product ID' => $magentoProduct->getId(),
                'Syncer' => $syncer->getName(),
            ]);

            $syncer->sync($magentoProduct);

            return;
        }

        throw new InvalidArgumentException("Could not find syncer for Stock Item for Magento Product #{$magentoProduct->getId()}.");
    }
}
