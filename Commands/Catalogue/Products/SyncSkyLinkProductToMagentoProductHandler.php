<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use InvalidArgumentException;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSyncCompositeProductRerunManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Exceptions\Products\SkyLinkProductDoesNotExistException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\CompositeProduct as CompositeSkyLinkProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepositoryFactory as SkyLinkProductRepositoryFactory;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;

class SyncSkyLinkProductToMagentoProductHandler
{
    private $skyLinkProductRepositoryFactory;

    private $syncers = [];

    private $compositeProductRerunManager;

    /**
     * Event Manager instance.
     *
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    /**
     * Create a new Sync SkyLink Product to Magento Product Handler.
     *
     * @param SkyLinkProductRepository                         $skyLinkProductRepositoryFactory
     * @param SkyLinkProductToMagentoProductSyncerInterface[]  $syncers
     * @param MagentoSyncCompositeProductRerunManagerInterface $compositeProductRerunManager
     * @param SkyLinkLoggerInterface                           $logger
     * @param EventManagerInterface                            $eventManager
     */
    public function __construct(
        SkyLinkProductRepositoryFactory $skyLinkProductRepositoryFactory,
        array $syncers,
        MagentoSyncCompositeProductRerunManagerInterface $compositeProductRerunManager,
        SkyLinkLoggerInterface $logger,
        EventManagerInterface $eventManager
    ) {
        $this->skyLinkProductRepositoryFactory = $skyLinkProductRepositoryFactory;

        array_walk($syncers, function (SkyLinkProductToMagentoProductSyncerInterface $syncer) {
            $this->syncers[] = $syncer;
        });

        $this->compositeProductRerunManager = $compositeProductRerunManager;
        $this->logger = $logger;
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

        // @todo should this be located here or in the repository?
        if (null === $skyLinkProduct) {
            $e = SkyLinkProductDoesNotExistException::withSkyLinkProductId($skyLinkProductId);

            $this->logger->error('SkyLink Product does not exist on the Retail Express API, is it part of a package?', [
                'Error' => $e->getMessage(),
                'SkyLink Product ID' => $skyLinkProductId,
            ]);

            throw $e;
        }

        // If we're not allowed to proceed, we'll just
        if (
            $this->caresAboutCompositeProductReruns($command, $skyLinkProduct) &&
            false === $this->compositeProductRerunManager->canProceedWithSync($skyLinkProduct)
        ) {
            $this->logger->info('Skipping syncing SkyLink Product to Magento Product because it is part of a SkyLink Composite Product that was recently synced and does not need to be re-synced yet.', [
                'SkyLink Product ID' => $skyLinkProductId,
            ]);

            // We don't need to dispatch an event becuase the reruns do not occur
            // in conjunction with any observers that watch that event (e.g. EDS)
            return;
        }

        foreach ($this->syncers as $syncer) {
            if (!$syncer->accepts($skyLinkProduct)) {
                continue;
            }

            $this->logger->info('Syncing SkyLink Product to Magento Product', [
                'SkyLink Product ID' => $skyLinkProduct->getId(),
                'SkyLink Product SKU' => $skyLinkProduct->getSku(),
                'Syncer' => $syncer->getName(),
            ]);

            $magentoProduct = $syncer->sync($skyLinkProduct);
            goto success;
        }

        throw new InvalidArgumentException("Could not find syncer for SkyLink Product #{$skyLinkProductId}.");

        success:

        if ($this->caresAboutCompositeProductReruns($command, $skyLinkProduct)) {
            $this->compositeProductRerunManager->didSync($skyLinkProduct);
        }

        $this->eventManager->dispatch(
            'retail_express_skylink_skylink_product_was_synced_to_magento_product',
            [
                'command' => $command,
                'skylink_product' => $skyLinkProduct,
                'magento_product' => $magentoProduct,
            ]
        );
    }

    private function caresAboutCompositeProductReruns(
        SyncSkyLinkProductToMagentoProductCommand $command,
        SkyLinkProduct $skyLinkProduct
    ) {
        return $skyLinkProduct instanceof CompositeSkyLinkProduct &&
            true === $command->potentialCompositeProductRerun;
    }
}
