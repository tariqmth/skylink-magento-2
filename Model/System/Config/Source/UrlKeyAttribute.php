<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;

class UrlKeyAttribute implements ArrayInterface
{
    private $magentoAttributeRepository;

    public function __construct(MagentoAttributeRepositoryInterface $magentoAttributeRepository)
    {
        $this->magentoAttributeRepository = $magentoAttributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return array_map(function (EavAttributeInterface $magentoAttribute) {
            return [
                'value' => $magentoAttribute->getAttributeCode(),
                'label' => $magentoAttribute->getDefaultFrontendLabel(),
            ];
        }, $this->magentoAttributeRepository->getMagentoAttributes());
    }
}
