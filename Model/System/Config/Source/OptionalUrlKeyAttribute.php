<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Model\Catalogue\Attributes\MagentoAttributeType;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;

class OptionalUrlKeyAttribute implements ArrayInterface
{
    private $urlKeyAttribute;

    public function __construct(UrlKeyAttribute $urlKeyAttribute)
    {
        $this->urlKeyAttribute = $urlKeyAttribute;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        $options = $this->urlKeyAttribute->toOptionArray();

        array_unshift($options, [
            'value' => 0,
            'label' => __('None'),
        ]);

        return $options;
    }
}
