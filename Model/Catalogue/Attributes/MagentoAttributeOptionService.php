<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Swatches\Model\Swatch;
use Magento\Eav\Api\AttributeRepositoryInterface;
use RetailExpress\SkyLink\Exceptions\Products\TextSwatchZeroException;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use Magento\Eav\Model\Entity\Attribute\Source\TableFactory;

class MagentoAttributeOptionService implements MagentoAttributeOptionServiceInterface
{
    use MagentoAttributeOption;

    private $magentoAttributeOptionFactory;

    private $magentoAttributeRepository;

    /**
     * Logger instance.
     *
     * @var SkyLinkLoggerInterface
     */
    private $logger;

    private $tableFactory;

    /**
     * Create a new Magento Attribute Option Service.
     *
     * @param ResourceConnection                 $resourceConnection
     * @param AttributeOptionManagementInterface $magentoAttributeOptionManagement
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AttributeOptionManagementInterface $magentoAttributeOptionManagement,
        AttributeOptionInterfaceFactory $magentoAttributeOptionFactory,
        SwatchHelper $swatchHelper,
        AttributeRepositoryInterface $magentoAttributeRepository,
        SkyLinkLoggerInterface $logger,
        TableFactory $tableFactory
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->magentoAttributeOptionManagement = $magentoAttributeOptionManagement;
        $this->magentoAttributeOptionFactory = $magentoAttributeOptionFactory;
        $this->swatchHelper = $swatchHelper;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
        $this->logger = $logger;
        $this->tableFactory = $tableFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function mapMagentoAttributeOptionForSkyLinkAttributeOption(
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $skyLinkAttributeCode = $skyLinkAttributeOption->getAttribute()->getCode();
        $magentoAttributeOptionId = $magentoAttributeOption->getId();

        if ($this->mappingExists($skyLinkAttributeOption)) {
            $this->connection->update(
                $this->getAttributeOptionsTable(),
                ['magento_attribute_option_id' => $magentoAttributeOptionId],
                [
                    'skylink_attribute_code = ?' => $skyLinkAttributeCode,
                    'skylink_attribute_option_id = ?' => $skyLinkAttributeOption->getId(),
                ]
            );
        } else {
            $this->connection->insert(
                $this->getAttributeOptionsTable(),
                [
                    'skylink_attribute_code' => $skyLinkAttributeCode,
                    'skylink_attribute_option_id' => $skyLinkAttributeOption->getId(),
                    'magento_attribute_option_id' => $magentoAttributeOptionId,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createMagentoAttributeOptionForSkyLinkAttributeOption(
        ProductAttributeInterface $magentoAttribute,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $typeId = $magentoAttribute->getEntityTypeId();
        $attributeCode = $magentoAttribute->getAttributeCode();
        $attributeLabel = (string) $skyLinkAttributeOption->getLabel();
        $magentoAttributeOption = $this->magentoAttributeOptionFactory->create();
        $magentoAttributeOption->setLabel($attributeLabel);

        if ($this->swatchHelper->isVisualSwatch($magentoAttribute)) {
            // Get the attribute as an EAV model rather than catalog
            $magentoAttribute = $this->magentoAttributeRepository->get($typeId, $attributeCode);
            $this->addSwatch($magentoAttribute, $attributeLabel, 'visual');
        } elseif ($this->swatchHelper->isTextSwatch($magentoAttribute)) {
            // Bug which causes "0" values to be invalid
            if (empty($attributeLabel)) {
                $e = TextSwatchZeroException::withSkylinkAttributeOption($skyLinkAttributeOption);
                $this->logger->error($e->getMessage());
                throw $e;
            }
            $magentoAttribute = $this->magentoAttributeRepository->get($typeId, $attributeCode);
            $this->addSwatch($magentoAttribute, $attributeLabel, 'text');
        } else {
            $this->saveMagentoAttributeOption($magentoAttribute, $magentoAttributeOption);
        }

        // Use new source model to prevent using cached _options values under getAllOptions()
        $sourceModel = $this->tableFactory->create();
        $sourceModel->setAttribute($magentoAttribute);
        $magentoAttributeOptionId = $sourceModel->getOptionId($attributeLabel);

        $magentoAttributeOption->setValue($magentoAttributeOptionId);
        return $magentoAttributeOption;
    }

    public function updateMagentoAttributeOptionForSkyLinkAttributeOption(
        ProductAttributeInterface $magentoAttribute,
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        // If the labels match, we will skip on the overhead of actually saving
        if ($magentoAttributeOption->getValue() == $skyLinkAttributeOption->getLabel()) {
            return;
        }

        // For some reason, the retrieved attribute has the value property set to the frontend text, e.g. "Large",
        // and the label property is undefined. In order to save correctly, we need the value to be the option ID,
        // and the label property to be set to the text.
        $magentoAttributeOption->setValue($magentoAttributeOption->getId());
        $magentoAttributeOption->setLabel((string) $skyLinkAttributeOption->getLabel());
        $this->saveMagentoAttributeOption($magentoAttribute, $magentoAttributeOption);
    }

    private function saveMagentoAttributeOption(
        ProductAttributeInterface $magentoAttribute,
        AttributeOptionInterface $magentoAttributeOption
    ) {
        $this->magentoAttributeOptionManagement->add(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $magentoAttribute->getAttributeCode(),
            $magentoAttributeOption
        );
    }

    /**
     * @todo remove this once AttributeOptionInterfaceFactory updates the value / label of
     * an attribute option after inserting in the database. We're currently coupling an
     * assumption that whatever interface we have is actually using the same SQL-based
     * database as us, which is naughty
     */
    private function getLastAddedOptionIdForMagentoAttribute(ProductAttributeInterface $magentoAttribute)
    {
        return $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->connection->getTableName('eav_attribute_option'), 'option_id')
                ->where('attribute_id = ?', $magentoAttribute->getAttributeId())
                ->order('option_id desc')
        );
    }

    private function mappingExists(SkyLinkAttributeOption $skyLinkAttributeOption)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getAttributeOptionsTable(), 'count(magento_attribute_option_id)')
                ->where('skylink_attribute_code = ?', $skyLinkAttributeOption->getAttribute()->getCode())
                ->where('skylink_attribute_option_id = ?', $skyLinkAttributeOption->getId())
        );
    }

    private function addSwatch($magentoAttribute, $swatchLabel, $swatchType)
    {
        $values = [(string) $swatchLabel];
        $data = $this->generateSwatchOptions($values, $swatchType);
        $magentoAttribute->addData($data);
        $magentoAttribute->save();
        return $magentoAttribute;
    }

    private function generateSwatchOptions(array $values, $swatchType)
    {
        if (empty($values)) {
            return;
        }

        $i = 0;
        foreach($values as $value) {
            $order["option_{$i}"] = $i;

            $optionsStore["option_{$i}"] = array(
                0 => $value, // admin
                1 => '' // default store view
            );

            $visualSwatch["option_{$i}"] = '';

            $delete["option_{$i}"] = '';

            $i++;
        }

        switch($swatchType)
        {
            case 'text':
                return [
                    'optiontext' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                    'swatchtext' => [
                        'value'     => $optionsStore,
                    ],
                ];
                break;
            case 'visual':
                return [
                    'optionvisual' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                    'swatchvisual' => [
                        'value'     => $visualSwatch,
                    ],
                ];
                break;
            default:
                return [
                    'option' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                ];
        }
    }
}
