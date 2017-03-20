<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface as BaseProductAttributeRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeServiceInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeTypeManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Exceptions\Products\AttributeNotMappedException;
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

    private $magentoAttributeTypeManager;

    private $magentoAttributeOptionRepository;

    private $magentoAttributeOptionService;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    /**
     * Event Manager instance.
     *
     * @var EventManagerInterface
     */
    private $eventManager;

    public function __construct(
        ConfigInterface $config,
        AttributeRepositoryFactory $attributeRepositoryFactory,
        BaseProductAttributeRepositoryInterface $baseMagentoProductAttributeRepository,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository,
        MagentoAttributeServiceInterface $magentoAttributeService,
        MagentoAttributeTypeManagerInterface $magentoAttributeTypeManager,
        MagentoAttributeOptionRepositoryInterface $magentoAttributeOptionRepository,
        MagentoAttributeOptionServiceInterface $magentoAttributeOptionService,
        SkyLinkLoggerInterface $logger,
        EventManagerInterface $eventManager
    ) {
        $this->config = $config;
        $this->attributeRepositoryFactory = $attributeRepositoryFactory;
        $this->baseMagentoProductAttributeRepository = $baseMagentoProductAttributeRepository;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
        $this->magentoAttributeService = $magentoAttributeService;
        $this->magentoAttributeTypeManager = $magentoAttributeTypeManager;
        $this->magentoAttributeOptionRepository = $magentoAttributeOptionRepository;
        $this->magentoAttributeOptionService = $magentoAttributeOptionService;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
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

        $magentoAttributeCode = $command->magentoAttributeCode;

        // If we specified a new attribute to map
        if (null !== $magentoAttributeCode) {
            /* @var ProductAttributeInterface $magentoAttributeToMap */
            $magentoAttributeToMap = $this->baseMagentoProductAttributeRepository->get($command->magentoAttributeCode);

            $this->logger->info('Syncing SkyLink Attribute to Magento Attribute.', [
                'SkyLink Attribute Code' => $skyLinkAttributeCode,
                'Magento Attribute Code' => $magentoAttributeToMap->getAttributeCode(),
            ]);

        // Otherwise, find the existing one
        } else {

            $this->logger->info('Finding an already mapped Magento Attribute to sync SkyLink Attribute to.', [
                'SkyLink Attribute Code' => $skyLinkAttributeCode,
            ]);

            /* @var ProductAttributeInterface|null $magentoAttributeToMap */
            $magentoAttributeToMap = $this->getAlreadyMappedMagentoAttribute($skyLinkAttribute);

            if (null === $magentoAttributeToMap) {
                $e = AttributeNotMappedException::withSkyLinkAttributeCode($skyLinkAttributeCode);

                $this->logger->error($e->getMessage(), [
                    'SkyLink Attribute Code' => $skyLinkAttributeCode,
                ]);

                throw $e;
            }

            $this->logger->info('Syncing SkyLink Attribute to already mapped Magento Attribute.', [
                'SkyLink Attribute Code' => $skyLinkAttributeCode,
                'Magento Attribute Code' => $magentoAttributeToMap->getAttributeCode(),
            ]);
        }

        // Remap the attribute only if needed
        $this->remapAttributeOnlyIfNeeded($magentoAttributeToMap, $skyLinkAttribute);

        /* @var \RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType $magentoAttributeType */
        $magentoAttributeType = $this->magentoAttributeTypeManager->getType($magentoAttributeToMap);

        if (!$magentoAttributeType->usesOptions()) {
            $this->logger->info('Magento Attribute Does does not use options, finishing sync.', [
                'SkyLink Attribute Code' => $skyLinkAttributeCode,
                'Magento Attribute Code' => $magentoAttributeToMap->getAttributeCode(),
            ]);

            return;
        }

        // And sync our new attribute mappings
        $this->syncAttributeOptionMappings($magentoAttributeToMap, $skyLinkAttribute);

        $this->eventManager->dispatch(
            'retail_express_skylink_skylink_attribute_was_synced_to_magento_attribute',
            [
                'command' => $command,
                'skylink_attribute' => $skyLinkAttribute,
                'magento_attribute' => $magentoAttributeToMap,
            ]
        );
    }

    private function remapAttributeOnlyIfNeeded(
        ProductAttributeInterface $magentoAttributeToMap,
        SkyLinkAttribute $skyLinkAttribute
    ) {
        /* @var ProductAttributeInterface|null $alreadyMappedMagentoAttribute */
        $alreadyMappedMagentoAttribute = $this->getAlreadyMappedMagentoAttribute($skyLinkAttribute);

        if (null === $alreadyMappedMagentoAttribute ||
            $alreadyMappedMagentoAttribute->getAttributeCode() !== $magentoAttributeToMap->getAttributeCode()
        ) {
            // Map to the new attribute, which removes all old previous mappings
            $this
                ->magentoAttributeService
                ->mapMagentoAttributeForSkyLinkAttributeCode($magentoAttributeToMap, $skyLinkAttribute->getCode());
        }
    }

    /**
     * @return ProductAttributeInterface|null
     */
    private function getAlreadyMappedMagentoAttribute($skyLinkAttribute)
    {
        // @todo make this more effecient with local caching
        // Check if we're dealing with the same attribute or not. If we aren't, we'll map to the new one
        return $this
            ->magentoAttributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttribute->getCode());
    }

    private function syncAttributeOptionMappings(
        ProductAttributeInterface $magentoAttribute,
        SkyLinkAttribute $skyLinkAttribute
    ) {
        $skyLinkAttributeOptions = $skyLinkAttribute->getOptions();

        $this->logger->debug('Mapping SkyLink Attribute Options to Magento Attribute Options.', [
            'SkyLink Attribute Code' => $skyLinkAttribute->getCode(),
            'Magento Attribute Code' => $magentoAttribute->getAttributeCode(),
            'Number of Options' => count($skyLinkAttributeOptions),
        ]);

        array_map(function (SkyLinkAttributeOption $skyLinkAttributeOption) use ($skyLinkAttribute, $magentoAttribute) {

            // Let's see if there's a mapping in place
            $mappedMagentoAttributeOption = $this
                ->magentoAttributeOptionRepository
                ->getMappedMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

            // If there's a mapping already, nothing further needs to happen
            if (null !== $mappedMagentoAttributeOption) {
                $this->logger->debug(
                    'SkyLink Attribute Option is already mapped to an appropriate Magento Attribute Option, updating it.',
                    [
                        'SkyLink Attribute Code' => $skyLinkAttribute->getCode(),
                        'SkyLink Attribute Option ID' => $skyLinkAttributeOption->getId(),
                        'SkyLink Attribute Option Label' => $skyLinkAttributeOption->getLabel(),
                        'Magento Attribute Option Value' => $mappedMagentoAttributeOption->getValue(),
                        'Magento Attribute Option Label' => $mappedMagentoAttributeOption->getLabel(),
                    ]
                );

                $this
                    ->magentoAttributeOptionService
                    ->updateMagentoAttributeOptionForSkyLinkAttributeOption(
                        $magentoAttribute,
                        $mappedMagentoAttributeOption,
                        $skyLinkAttributeOption
                    );

                return;
            }

            // Now, we'll try grab a possible Magento Attribute Option to set a new mapping against
            $magentoAttributeOptionToMap = $this
                ->magentoAttributeOptionRepository
                ->getPossibleMagentoAttributeOptionForSkyLinkAttributeOption($skyLinkAttributeOption);

            // Failing this, we'll create a new option
            // @todo move this to the Magento attribute option service
            if (null === $magentoAttributeOptionToMap) {
                $this->logger->debug(
                    'Couldn\'t find an appropriate Magento Attribute Option to map the SkyLink Attribute Option to, creating a new one.',
                    [
                        'SkyLink Attribute Code' => $skyLinkAttribute->getCode(),
                        'SkyLink Attribute Option ID' => $skyLinkAttributeOption->getId(),
                        'SkyLink Attribute Option Label' => $skyLinkAttributeOption->getLabel(),
                    ]
                );

                $magentoAttributeOptionToMap = $this
                    ->magentoAttributeOptionService
                    ->createMagentoAttributeOptionForSkyLinkAttributeOption($magentoAttribute, $skyLinkAttributeOption);
            }

            $this->logger->debug(
                'Found an existing, unmapped Magento Attribute Option that was appropriate to map the SkyLink Attribute Option to.',
                [
                    'SkyLink Attribute Code' => $skyLinkAttribute->getCode(),
                    'SkyLink Attribute Option ID' => $skyLinkAttributeOption->getId(),
                    'SkyLink Attribute Option Label' => $skyLinkAttributeOption->getLabel(),
                    'Magento Attribute Option Value' => $magentoAttributeOptionToMap->getValue(),
                    'Magento Attribute Option Label' => $magentoAttributeOptionToMap->getLabel(),
                ]
            );

            // Now we can set the mapping for our Magento attribute option
            $this->magentoAttributeOptionService->mapMagentoAttributeOptionForSkyLinkAttributeOption(
                $magentoAttributeOptionToMap,
                $skyLinkAttributeOption
            );
        }, $skyLinkAttributeOptions);
    }
}
