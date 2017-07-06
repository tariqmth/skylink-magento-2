<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Model\Catalogue\Products\QuantityCalculation as BaseQuantityCalculation;

class QuantityCalculation implements ArrayInterface
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
                'label' => BaseQuantityCalculation::get($value)->getLabel(),
            ];
        }, BaseQuantityCalculation::getConstants());
    }
}
