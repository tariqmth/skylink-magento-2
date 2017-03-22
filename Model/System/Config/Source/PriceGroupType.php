<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;

class PriceGroupType implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return array_map(function ($value) {
            return [
                'value' => $value,
                'label' => SkyLinkPriceGroupType::get($value)->getPriceGroupTypeName(),
            ];
        }, SkyLinkPriceGroupType::getConstants());
    }
}
