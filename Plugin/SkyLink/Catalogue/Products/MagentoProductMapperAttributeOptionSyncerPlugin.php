<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\MagentoProductMapperInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Attributes\SyncSkyLinkAttributeToMagentoAttributeCommand;
use RetailExpress\SkyLink\Exceptions\Products\AttributeOptionNotMappedException;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Product as SkyLinkProduct;

class MagentoProductMapperAttributeOptionSyncerPlugin
{
    private $commandBus;

    private $logger;

    public function __construct(CommandBusInterface $commandBus, SkyLinkLoggerInterface $logger)
    {
        $this->commandBus = $commandBus;
        $this->logger = $logger;
    }

    public function aroundMapMagentoProduct(
        MagentoProductMapperInterface $subject,
        callable $proceed,
        ProductInterface $magentoProduct,
        SkyLinkProduct $skyLinkProduct
    ) {
        do {
            try {
                $response = $proceed($magentoProduct, $skyLinkProduct);
                $retry = false;
            } catch (AttributeOptionNotMappedException $e) {
                $skyLinkAttributeOption = $e->getSkyLinkAttributeOption();

                $this->logger->error('During product sync, a new SkyLink Attribute Option was found, resyncing Attribute...', [
                    'Error' => $e->getMessage(),
                    'SkyLink Attribute Code' => $skyLinkAttributeOption->getAttribute()->getCode(),
                ]);

                dump($skyLinkAttributeOption);

                $command = new SyncSkyLinkAttributeToMagentoAttributeCommand();
                $command->skyLinkAttributeCode = (string) $skyLinkAttributeOption->getAttribute()->getCode();
                $command->shouldBeQueued = false;

                $this->commandBus->handle($command);
                $retry = true;
            }
        } while (true === $retry);

        return $response;
    }
}
