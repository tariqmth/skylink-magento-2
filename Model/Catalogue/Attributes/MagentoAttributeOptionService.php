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

class MagentoAttributeOptionService implements MagentoAttributeOptionServiceInterface
{
    use MagentoAttributeOption;

    private $magentoAttributeOptionFactory;

    private $magentoAttributeRepository;

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
        AttributeRepositoryInterface $magentoAttributeRepository
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->magentoAttributeOptionManagement = $magentoAttributeOptionManagement;
        $this->magentoAttributeOptionFactory = $magentoAttributeOptionFactory;
        $this->swatchHelper = $swatchHelper;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function mapMagentoAttributeOptionForSkyLinkAttributeOption(
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $skyLinkAttributeCode = $skyLinkAttributeOption->getAttribute()->getCode();
        $magentoAttributeOptionId = $this->getIdFromMagentoAttributeOption($magentoAttributeOption);

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
        $magentoAttributeOption = $this->magentoAttributeOptionFactory->create();
        $magentoAttributeOption->setLabel($skyLinkAttributeOption->getLabel());
        if ($this->swatchHelper->isSwatchAttribute($magentoAttribute)) {
            $magentoAttribute = $this->magentoAttributeRepository->get($typeId, $attributeCode);
            $this->addSwatch($magentoAttribute, $skyLinkAttributeOption->getLabel(), 'visual');
            $magentoAttributeOptionId = $magentoAttribute->getSource()
                ->getOptionId($skyLinkAttributeOption->getLabel());
        } elseif ($this->swatchHelper->isTextSwatch($magentoAttribute)) {
            $this->addSwatch($magentoAttribute, $skyLinkAttributeOption->getLabel(), 'text');
//            $magentoAttributeOption = $this->magentoAttributeOptionManagement->getItems(
//                $magentoAttribute->getEntityTypeId(), $skyLinkAttributeOption->getAttribute())->first();
            $magentoAttributeOptionId = $magentoAttribute->getSource()
                ->getOptionId($skyLinkAttributeOption->getLabel());
        } else {
            $this->saveMagentoAttributeOption($magentoAttribute, $magentoAttributeOption);
            // Unfortuantely, the Magento Attribute Option Management implementation does
            // not update the given attribute option's properties, so we'll query the
            // database ourselves to find out what the last added id was.
            $magentoAttributeOptionId = $this->getLastAddedOptionIdForMagentoAttribute($magentoAttribute);
        }

        $magentoAttributeOption->setValue($magentoAttributeOptionId);

        return $magentoAttributeOption;
    }

    public function updateMagentoAttributeOptionForSkyLinkAttributeOption(
        ProductAttributeInterface $magentoAttribute,
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        // If the labels match, we will skip on the overhead of actually saving
        if ($magentoAttributeOption->getLabel() == $skyLinkAttributeOption->getLabel()) {
            return;
        }

        $magentoAttributeOption->setLabel($skyLinkAttributeOption->getLabel());
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

            $textSwatch["option_{$i}"] = array(
                1 => $value,
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
                        'value'     => $textSwatch,
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
