<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use InvalidArgumentException;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepository as SkyLinkProductRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;

class SyncSkyLinkProductToMagentoProductHandler
{
    private $skyLinkProductRepository;

    private $syncers = [];

    public function __construct(
        SkyLinkProductRepository $skyLinkProductRepository,
        array $syncers
    ) {
        $this->skyLinkProductRepository = $skyLinkProductRepository;

        array_walk($syncers, function (SkyLinkProductToMagentoProductSyncerInterface $syncer) {
            $this->syncers[] = $syncer;
        });
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

        $product = $this->skyLinkProductRepository->find($skyLinkProductId, $salesChannelId);

        foreach ($this->syncers as $syncer) {
            if (!$syncer->accepts($product)) {
                continue;
            }

            $syncer->sync($product);
            goto success;
        }

        throw new InvalidArgumentException("Could not find syncer for SkyLink Product #{$skyLinkProductId}.");

        success:
    }
}
