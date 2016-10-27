<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Attributes;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface as BaseProductAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeServiceInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepositoryFactory;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class SyncSkyLinkAttributeToMagentoAttributeHandler
{
    private $config;

    private $attributeRepositoryFactory;

    private $baseMagentoProductAttributeRepository;

    private $magentoAttributeRepository;

    private $magentoAttributeService;

    private $magentoAttributeOptionRepository;

    private $magentoAttributeOptionService;

    public function __construct(
        ConfigInterface $config,
        AttributeRepositoryFactory $attributeRepositoryFactory,
        BaseProductAttributeRepositoryInterface $baseMagentoProductAttributeRepository,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository,
        MagentoAttributeServiceInterface $magentoAttributeService,
        MagentoAttributeOptionRepositoryInterface $magentoAttributeOptionRepository,
        MagentoAttributeOptionServiceInterface $magentoAttributeOptionService
    ) {
        $this->config = $config;
        $this->attributeRepositoryFactory = $attributeRepositoryFactory;
        $this->baseMagentoProductAttributeRepository = $baseMagentoProductAttributeRepository;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
        $this->magentoAttributeService = $magentoAttributeService;
        $this->magentoAttributeOptionRepository = $magentoAttributeOptionRepository;
        $this->magentoAttributeOptionService = $magentoAttributeOptionService;
    }

    public function handle(SyncSkyLinkAttributeToMagentoAttributeCommand $command)
    {
        /** @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepository **/
        $attributeRepository = $this->attributeRepositoryFactory->create();

        // Grab our attribute
        $skyLinkAttribute = $attributeRepository->find(
            SkyLinkAttributeCode::get($command->skyLinkAttributeCode),
            $this->config->getSalesChannelId()
        );
        $skyLinkAttributeCode = $skyLinkAttribute->getCode();

        $magentoAttribute = $this->baseMagentoProductAttributeRepository->get($command->magentoAttributeCode);

        // Firstly, let's check if there's already a mapping present
        if ($this->magentoAttributeRepository->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode)) {
            $this->removeExistingAttributeOptionMappings();
        }

        // Now, map to the new one
    }

    private function removeExistingAttributeOptionMappings(SkyLinkAttributeCode $skyLinkAttributeCode)
    {
        foreach ($skyLinkAttribute->getOptions() as $skyLinkAttributeOption) {
            $magentoAttributeOption = $this
                ->magentoAttributeOptionRepository
                ->getMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

            if (null === $magentoAttributeOption) {
                continue;
            }

            $this->magentoAttributeOptionService->forgetMagentoAttributeOptionForSkyLinkAttributeOption(
                $magentoAttributeOption,
                $skyLinkAttributeOption
            );
        }
    }
}
