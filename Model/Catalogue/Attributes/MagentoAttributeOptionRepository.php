<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeOptionRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MagentoAttributeOptionRepository extends MagentoAttributeOptionRepositoryInterface
{
    use MagentoAttributeOption;

    /**
     * The Magento Attribute Option Managmeent instance.
     *
     * @var AttributeOptionManagementInterface
     */
    private $magentoAttributeOptionManagement;

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
    public function getMagentoAttributeOptionForSkyLinkAttributeOption(SkyLinkAttributeOption $skyLinkAttributeOption)
    {
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

        $magentoAttributeOption = $this->extractMagentoAttributeOptionById(
            $skyLinkAttributeCode,
            $magentoAttributeOptionId
        );

        // If we had a mapping in existence but the attribute option does not,
        // something's wrong with the DB, so we should pick it up here.
        if (null === $magentoAttributeOption) {
            throw new NoSuchEntityException(__(
                'Expected to find an attribute option #%magentoAttributeOptionId for attribute "%magentoAttributeCode".',
                compact($magentoAttributeOptionId, $magentoAttributeCode)
            ));
        }
    }

    private function extractMagentoAttributeOptionById(SkyLinkAttributeCode $skyLinkAttributeCode, $magentoAttributeOptionId)
    {
        $magentoAttributeCode = $this
            ->magentoAttributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skyLinkAttributeCode); // @todo "null" check?

        /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $magentoAttributeOptions **/
        $magentoAttributeOptions = $this->magentoAttributeOptionManagement->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $magentoAttributeCode
        );

        foreach ($magentoAttributeOptions as $magentoAttributeOption) {

            if ($magentoAttributeOptionId == $this->getIdFromMagentoAttributeOption($magentoAttributeOption)) {
                goto success;
            }
        }

        return null;

        success:

        return $magentoAttributeOption;
    }
}
