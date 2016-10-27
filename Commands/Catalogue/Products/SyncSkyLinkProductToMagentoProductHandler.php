<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use InvalidArgumentException;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepositoryFactory as SkyLinkProductRepositoryFactory;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;

class SyncSkyLinkProductToMagentoProductHandler
{
    private $skyLinkProductRepositoryFactory;

    private $syncers = [];

    /**
     * Event Manager instance.
     *
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * Create a new Sync SkyLink Product to Magento Product Handler.
     *
     * @param SkyLinkProductRepository                        $skyLinkProductRepositoryFactory
     * @param SkyLinkProductToMagentoProductSyncerInterface[] $syncers
     * @param EventManagerInterface                           $eventManager
     */
    public function __construct(
        SkyLinkProductRepositoryFactory $skyLinkProductRepositoryFactory,
        array $syncers,
        EventManagerInterface $eventManager
    ) {
        $this->skyLinkProductRepositoryFactory = $skyLinkProductRepositoryFactory;

        array_walk($syncers, function (SkyLinkProductToMagentoProductSyncerInterface $syncer) {
            $this->syncers[] = $syncer;
        });

        $this->eventManager = $eventManager;
    }

    /**
     * Synchronise a product by firstly grabbing the product from SkyLink and then
     * attempts to match it to an existing Product in Magento, or create a new one.
     *
     * @param SyncSkyLinkProductToMagentoProductCommand $command
     */
    public function handle(SyncSkyLinkProductToMagentoProductCommand $command)
    {
        $skyLinkProductId = new SkyLinkProductId($command->skyLinkProductId);
        $salesChannelId = new SalesChannelId($command->salesChannelId);

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepository $skyLinkProductRepository */
        $skyLinkProductRepository = $this->skyLinkProductRepositoryFactory->create();

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\Product $skyLinkProduct */
        $skyLinkProduct = $skyLinkProductRepository->find($skyLinkProductId, $salesChannelId);

        foreach ($this->syncers as $syncer) {
            if (!$syncer->accepts($skyLinkProduct)) {
                continue;
            }

            $magentoProduct = $syncer->sync($skyLinkProduct);
            goto success;
        }

        throw new InvalidArgumentException("Could not find syncer for SkyLink Product #{$skyLinkProductId}.");

        success:

        $this->eventManager->dispatch(
            'retail_express_skylink_skylink_product_was_synced_to_magento_product',
            [
                'command' => $command,
                'skylink_product' => $skyLinkProduct,
                'magento_product' => $magentoProduct,
            ]
        );
    }
}
