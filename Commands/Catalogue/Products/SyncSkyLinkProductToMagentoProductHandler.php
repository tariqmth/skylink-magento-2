<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Products;

use InvalidArgumentException;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoSyncCompositeProductRerunManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkProductToMagentoProductSyncerInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Data\Catalogue\Products\SkyLinkProductInSalesChannelGroupInterfaceFactory;
use RetailExpress\SkyLink\Api\Data\Segregation\SalesChannelGroupInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Segregation\SalesChannelGroupRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Products\SkyLinkProductDoesNotExistException;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\CompositeProduct as CompositeSkyLinkProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepository as SkyLinkProductRepository;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductRepositoryFactory as SkyLinkProductRepositoryFactory;

class SyncSkyLinkProductToMagentoProductHandler
{
    private $config;

    private $skyLinkProductRepositoryFactory;

    private $skyLinkProductRepository;

    private $syncers = [];

    private $compositeProductRerunManager;

    private $salesChannelGroupRepository;

    private $skyLinkProductInSalesChannelGroupFactory;

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
     * @param ConfigInterface                                   $config
     * @param SkyLinkProductRepository                          $skyLinkProductRepositoryFactory
     * @param SkyLinkProductToMagentoProductSyncerInterface[]   $syncers
     * @param MagentoSyncCompositeProductRerunManagerInterface  $compositeProductRerunManager
     * @param SalesChannelGroupRepositoryInterface              $salesChannelGroupRepository
     * @param SkyLinkProductInSalesChannelGroupInterfaceFactory $skyLinkProductInSalesChannelGroupFactory
     * @param SkyLinkLoggerInterface                            $logger
     * @param EventManagerInterface                             $eventManager
     */
    public function __construct(
        ConfigInterface $config,
        SkyLinkProductRepositoryFactory $skyLinkProductRepositoryFactory,
        array $syncers,
        MagentoSyncCompositeProductRerunManagerInterface $compositeProductRerunManager,
        SalesChannelGroupRepositoryInterface $salesChannelGroupRepository,
        SkyLinkProductInSalesChannelGroupInterfaceFactory $skyLinkProductInSalesChannelGroupFactory,
        SkyLinkLoggerInterface $logger,
        EventManagerInterface $eventManager
    ) {
        $this->config = $config;
        $this->skyLinkProductRepositoryFactory = $skyLinkProductRepositoryFactory;

        array_walk($syncers, function (SkyLinkProductToMagentoProductSyncerInterface $syncer) {
            $this->syncers[] = $syncer;
        });

        $this->compositeProductRerunManager = $compositeProductRerunManager;
        $this->salesChannelGroupRepository = $salesChannelGroupRepository;
        $this->skyLinkProductInSalesChannelGroupFactory = $skyLinkProductInSalesChannelGroupFactory;
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

        /* @var SkyLinkProduct $skyLinkProduct */
        $skyLinkProduct = $this->getSkyLinkProduct($skyLinkProductId);

        // We'll now grab the product in the context of any additional Sales Channel Groups. We'll
        // use this to determine what websites to copy the product across to and also allow the
        // syncer to work on any specifics for the product in that Sales Channel Group.

        /* @var SkyLinkProductInSalesChannelGroupInterface[] $skyLinkProductInSalesChannelGroups */
        $skyLinkProductInSalesChannelGroups = $this->getSkyLinkProductInSalesChannelGroups($skyLinkProductId);

        // If not enabled for the global sales channel, try other sales channels
        if (null === $skyLinkProduct) {
            if (!empty($skyLinkProductInSalesChannelGroups)) {
                $skyLinkProduct = $skyLinkProductInSalesChannelGroups[0]->getSkyLinkProduct();
            } else {
                $e = SkyLinkProductDoesNotExistException::withSkyLinkProductId($skyLinkProductId);

                $this->logger->error('SkyLink Product does not exist on the Retail Express API, is it part of a package?', [
                    'Error' => $e->getMessage(),
                    'SkyLink Product ID' => $skyLinkProductId,
                ]);

                throw $e;
            }
        }

        // If we care about composite product reruns (i.e. on a bulk sync)
        if ($this->caresAboutCompositeProductReruns($command, $skyLinkProduct)) {

            // If we can't proceed because it's already been done recently
            if (false === $this->compositeProductRerunManager->canProceedWithSync($skyLinkProduct)) {
                $this->logger->info('Skipping syncing SkyLink Product to Magento Product because it is part of a SkyLink Composite Product that was recently synced and does not need to be re-synced yet.', [
                    'SkyLink Product ID' => $skyLinkProductId,
                ]);

                goto success;
            }

            // If we can sync, let's tell the re-run manager that we're starting right now
            $this->compositeProductRerunManager->isSyncing($skyLinkProduct);
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

            // Grab our Magento Product from the syncer (we'll use this in event dispatching later on)
            $magentoProduct = $syncer->sync(
                $skyLinkProduct,
                $skyLinkProductInSalesChannelGroups
            );

            goto success;
        }

        throw new InvalidArgumentException("Could not find syncer for SkyLink Product #{$skyLinkProductId}.");

        success:

        $this->eventManager->dispatch(
            'retail_express_skylink_skylink_product_was_synced_to_magento_product',
            [
                'command' => $command,
                'skylink_product' => $skyLinkProduct,
                'magento_product' => isset($magentoProduct) ? $magentoProduct : null, // During a composite product rerun the Magento product is never fetched
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

    private function getSkyLinkProduct(SkyLinkProductId $skyLinkProductId)
    {
        /* @var \RetailExpress\SkyLink\ValueObjects\SalesChannelId $salesChannelId */
        $salesChannelId = $this->config->getSalesChannelId();

        return $this->getSkyLinkProductRepository()->find($skyLinkProductId, $salesChannelId);
    }

    private function getSkyLinkProductInSalesChannelGroups(SkyLinkProductId $skyLinkProductId)
    {
        $salesChannelGroups = $this->salesChannelGroupRepository->getList();

        // We'll loop through the Sales Channel Groups and grab the product in the context of each
        $productInSalesChannelGroups = [];
        array_walk(
            $salesChannelGroups,
            function (SalesChannelGroupInterface $salesChannelGroup) use ($skyLinkProductId, &$productInSalesChannelGroups) {
                $skyLinkProduct = $this->getSkyLinkProductRepository()->find(
                    $skyLinkProductId,
                    $salesChannelGroup->getSalesChannelId()
                );

                if (null === $skyLinkProduct) {
                    return;
                }

                $productInSalesChannelGroup = $this->skyLinkProductInSalesChannelGroupFactory->create();
                $productInSalesChannelGroup->setSkyLinkProduct($skyLinkProduct);
                $productInSalesChannelGroup->setSalesChannelGroup($salesChannelGroup);

                $productInSalesChannelGroups[] = $productInSalesChannelGroup;
            }
        );

        return $productInSalesChannelGroups;
    }

    private function getSkyLinkProductRepository()
    {
        if (null === $this->skyLinkProductRepository) {
            $this->skyLinkProductRepository = $this->skyLinkProductRepositoryFactory->create();
        }

        return $this->skyLinkProductRepository;
    }
}
