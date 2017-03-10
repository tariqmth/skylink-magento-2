<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Catalog\Model\Config\Source\Price\Scope;

class PriceScope extends Scope
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        $modifiedOptions = array_filter(parent::toOptionArray(), function (array $option) {
            return $option['value'] != 0; // Non-scrict comparison
        });

        return array_values($modifiedOptions);
    }
}
