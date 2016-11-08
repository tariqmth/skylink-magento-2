<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MagentoAttributeOptionRepository implements MagentoAttributeOptionRepositoryInterface
{
    use MagentoAttributeOption;

    /**
     * The Magento Attribute Repository instance.
     *
     * @var MagentoAttributeRepositoryInterface
     */
    private $magentoAttributeRepository;

    /**
     * Create a new Magento Attribute Option Repository.
     *
     * @param ResourceConnection                  $resourceConnection
     * @param AttributeOptionManagementInterface  $magentoAttributeOptionManagement
     * @param MagentoAttributeRepositoryInterface $magentoAttributeRepository
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AttributeOptionManagementInterface $magentoAttributeOptionManagement,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->magentoAttributeOptionManagement = $magentoAttributeOptionManagement;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappedMagentoAttributeOptionForSkyLinkAttributeOption(
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $skyLinkAttributeCode = $skyLinkAttributeOption->getAttribute()->getCode();

        $magentoAttributeOptionId = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getAttributeOptionsTable(), 'magento_attribute_option_id')
                ->where('skylink_attribute_code = ?', $skyLinkAttributeCode)
                ->where('skylink_attribute_option_id = ?', $skyLinkAttributeOption->getId())
        );

        if (false === $magentoAttributeOptionId) {
            return null;
        }

        $magentoAttributeOption = $this->findMatchingMagentoAttributeOption(
            $skyLinkAttributeCode,
            function (AttributeOptionInterface $magentoAttributeOption) use ($magentoAttributeOptionId) {
                return $magentoAttributeOptionId == $this->getIdFromMagentoAttributeOption($magentoAttributeOption);
            }
        );

        // If we had a mapping but the attribute option does not exist for this mapping,
        // something's wrong with the DB, so we should pick it up here.
        if (null === $magentoAttributeOption) {
            throw new NoSuchEntityException(__(
                'SkyLink attribute "%skyLinkAttributeCode" has mapping for Option #%skyLinkAttributeOption to Magento Attribute Option #%magentoAttributeOptionId, but no such Magento Attribute Option exists.',
                compact('skyLinkAttributeCode', 'skyLinkAttributeOption', 'magentoAttributeOptionId')
            ));
        }

        return $magentoAttributeOption;
    }

    /**
     * {@inheritdoc}
     */
    public function getPossibleMagentoAttributeOptionForSkyLinkAttributeOption(
        SkyLinkAttributeOption $skyLinkAttributeOption
    ) {
        $skyLinkAttributeCode = $skyLinkAttributeOption->getAttribute()->getCode();

        return $this->findMatchingMagentoAttributeOption(
            $skyLinkAttributeCode,
            function (AttributeOptionInterface $magentoAttributeOption) use ($skyLinkAttributeOption) {
                return $skyLinkAttributeOption->getLabel() == $magentoAttributeOption->getLabel();
            }
        );
    }

    /**
     * Returns the first matching attribute option that pass a given test.
     *
     * @param SkyLinkAttributeCode $skyLinkAttributeCode
     * @param callableÂ            $callback
     * @param bool                 $findFirst            If set to "false", it will find the last match, not the first
     *
     * @return AttributeOptionInterface|null
     */
    private function findMatchingMagentoAttributeOption(SkyLinkAttributeCode $skyLinkAttributeCode, callable $callback, $findFirst = true)
    {
        /* @var AttributeOptionInterface[] $magentoAttributeOptions */
        $magentoAttributeOptions = $this->getMagentoAttributeOptions($skyLinkAttributeCode);

        if (false === $findFirst) {
            $magentoAttributeOptions = array_reverse($magentoAttributeOptions);
        }

        foreach ($magentoAttributeOptions as $magentoAttributeOption) {
            if (true === $callback($magentoAttributeOption)) {
                return $magentoAttributeOption;
            }
        }

        return null;
    }

    private function getMagentoAttributeOptions(SkyLinkAttributeCode $skyLinkAttributeCode)
    {
        /* @var \Magento\Catalog\Api\Data\ProductAttributeInterface $magentoAttribute */
        $magentoAttribute = $this
            ->magentoAttributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode);

        if (null === $magentoAttribute) {
            // @todo should we throw an exception here?
        }

        return $this->magentoAttributeOptionManagement->getItems(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $magentoAttribute->getAttributeCode()
        );
    }
}
