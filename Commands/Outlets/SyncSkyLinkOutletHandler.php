<?php

namespace RetailExpress\SkyLink\Commands\Outlets;

use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Outlets\SkyLinkOutletRepositoryInterface as LocalOutletRepository;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId;
use RetailExpress\SkyLink\Sdk\Outlets\OutletRepositoryFactory as RemoteOutletRepositoryFactory;

class SyncSkyLinkOutletHandler
{
    private $config;

    private $remoteOutletRepositoryFactory;

    private $localOutletRepository;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigInterface $config,
        RemoteOutletRepositoryFactory $remoteOutletRepositoryFactory,
        LocalOutletRepository $localOutletRepository,
        SkyLinkLoggerInterface $logger
    ) {
        $this->config = $config;
        $this->remoteOutletRepositoryFactory = $remoteOutletRepositoryFactory;
        $this->localOutletRepository = $localOutletRepository;
        $this->logger = $logger;
    }

    public function handle(SyncSkyLinkOutletCommand $command)
    {
        $skyLinkOutletId = new OutletId($command->skyLinkOutletId);

        $this->logger->info('Syncing SkyLink Outlet.', ['SkyLink Outlet ID' => $skyLinkOutletId]);

        /* \@var RetailExpress\SkyLink\Sdk\Outlets\OutletRepository $remoteOutletRepository */
        $remoteOutletRepository = $this->remoteOutletRepositoryFactory->create();

        $skyLinkOutlet = $remoteOutletRepository->find(
            $skyLinkOutletId,
            $this->config->getSalesChannelId()
        );

        $this->localOutletRepository->save($skyLinkOutlet);
    }
}
