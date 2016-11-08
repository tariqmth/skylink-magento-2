<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface as BaseProductAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeServiceInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepositoryFactory;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\Attribute as SkyLinkAttribute;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

// @todo refactor this - it smells so bad!
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
        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepository **/
        $attributeRepository = $this->attributeRepositoryFactory->create();

        // Grab our attribute
        $skyLinkAttribute = $attributeRepository->find(
            SkyLinkAttributeCode::get($command->skyLinkAttributeCode),
            $this->config->getSalesChannelId()
        );

        $skyLinkAttributeCode = $skyLinkAttribute->getCode();

        // Get the Magento attribute instance
        /* @var ProductAttributeInterface $magentoAttributeToMap */
        $magentoAttributeToMap = $this->baseMagentoProductAttributeRepository->get($command->magentoAttributeCode);

        // Remap the attribute only if needed
        $this->remapAttributeOnlyIfNeeded($magentoAttributeToMap, $skyLinkAttribute);

        // And sync our new attribute mappings
        $this->syncAttributeOptionMappings($magentoAttributeToMap, $skyLinkAttribute);
    }

    private function remapAttributeOnlyIfNeeded(
        ProductAttributeInterface $magentoAttributeToMap,
        SkyLinkAttribute $skyLinkAttribute
    ) {
        $skyLinkAttributeCode = $skyLinkAttribute->getCode();

        // Check if we're dealing with the same attribute or not. If we aren't, we'll map to the new one
        /* @var ProductAttributeInterface $alreadyMappedMagentoAttribute */
        $alreadyMappedMagentoAttribute = $this
            ->magentoAttributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);

        if (null === $alreadyMappedMagentoAttribute ||
            $alreadyMappedMagentoAttribute->getCode() !== $magentoAttributeToMap->getCode()
        ) {

            // Map to the new attribute, which removes all old previous mappings
            $this
                ->magentoAttributeService
                ->mapMagentoAttributeForSkyLinkAttributeCode($magentoAttributeToMap, $skyLinkAttributeCode);
        }
    }

    private function syncAttributeOptionMappings(
        ProductAttributeInterface $magentoAttribute,
        SkyLinkAttribute $skyLinkAttribute
    ) {
        array_map(function (SkyLinkAttributeOption $skyLinkAttributeOption) use ($magentoAttribute) {

            // Let's see if there's a mapping in place
            $hasExistingMapping = (bool) $this
                ->magentoAttributeOptionRepository
                ->getMappedMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

            // If there's a mapping already, nothing further needs to happen
            if (true === $hasExistingMapping) {
                continue;
            }

            // Now, we'll try grab a possible Magento Attribute Option to set a new mapping against
            $magentoAttributeOptionToMap = $this
                ->magentoAttributeOptionRepository
                ->getPossibleMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

            // Failing this, we'll create a new option
            // @todo move this to the Magento attribute option service
            if (null === $magentoAttributeOptionToMap) {
                $magentoAttributeOptionToMap = $this
                    ->magentoAttributeOptionService
                    ->createMagentoAttributeOptionForSkyLinkAttributeOption($magentoAttribute, $skyLinkAttributeOption);
            }

            // Now we can set the mapping for our Magento attribute option
            $this->magentoAttributeOptionService->mapMagentoAttributeOptionForSkyLinkAttributeOption(
                $magentoAttributeOptionToMap,
                $skyLinkAttributeOption
            );
        }, $skyLinkAttribute->getOptions());
    }
}
