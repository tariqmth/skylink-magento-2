<?php

use Magento\Eav\Api\Data\AttributeOptionInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MagentoAttributeOptionService extends MagentoAttributeOptionServiceInterface
{
    use MagentoAttributeOption;

    /**
     * Create a new Magento Attribute Option Service.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * Defines the Magento Attribute Option that represents the given
     * SkyLink Attribute Option.
     *
     * @param AttributeOptionInterface $magentoAttributeOption
     * @param SkyLinkAttributeOption   $skyLinkAttributeOption
     */
    public function mapMagentoAttributeOptionForSkyLinkAttributeOption(
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $skyLinkAttributeCode = $skyLinkAttributeOption->getAttribute()->getCode();

        if ($this->mappingExists($skyLinkAttributeOption)) {
            $this->connection->update(
                $this->getAttributeOptionsTable(),
                ['magento_attribute_option_id' => $this->getIdFromMagentoAttributeOption($magentoAttributeOption)],
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
                    'magento_attribute_option_id' => $this->getIdFromMagentoAttributeOption($magentoAttributeOption),
                ]
            );
        }
    }

    /**
     * Removes the definition of the Magento Attribute Option
     * that represents the given SkyLink Attribute Option.
     *
     * @param AttributeOptionInterface $magentoAttributeOption
     * @param SkyLinkAttributeOption   $skyLinkAttributeOption
     */
    public function forgetMagentoAttributeOptionForSkyLinkAttributeOption(
        AttributeOptionInterface $magentoAttributeOption,
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        if (!$this->mappingExists($skyLinkAttributeOption)) {
            return null; // @todo should we throw an exception? Probably
        }

        $this->connection->delete(
            $this->getAttributeOptionsTable(),
            [
                'skylink_attribute_code = ?' => $skyLinkAttributeOption->getAttribute()->getCode(),
                'skylink_attribute_option_id = ?' => $skyLinkAttributeOption->getId(),
            ]
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
}
